$(document).ready(function () {
    let selectedTeam = null;
    let selectedDecision = null;
    let matchId = $('input[name="toss_match_id"]').val();
    let bowlingTeamPlayers = [];
    let matchState = {
        striker: null,
        nonStriker: null,
        battingTeamId: null
    };

    let strikerId = null;
    let nonStrikerId = null;
    const stateKey = "match_state_" + matchId;
    let currentExtra = null;
    let currentWicket = null;

    $("#start-match").select2({
        placeholder: "Select Status",
        width: "25%",
        minimumResultsForSearch: -1
    });

    $('#start-match').on('change', function () {
        var selectedStatus = $(this).val();
        console.log("Selected status:", selectedStatus);

        $.ajax({
            url: '/admin/cricket-matches/start/' + matchId,
            method: 'GET',
            data: {
                status: selectedStatus,
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Status Updated!',
                        text: `Match status has been changed to "${selectedStatus}".`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function (err) {
                Swal.fire('Error', 'Failed to update match status.', 'error');
            }
        });
    });


    // ------------------------
    // 🔹 Extra Runs
    // ------------------------
    document.querySelectorAll('.btn-extra').forEach(btn => {
        btn.addEventListener('click', function () {
            let extra = this.dataset.extra;
            document.querySelector('#extraModal .modal-title').innerText = "Selected: " +
                extra;
        });
    });

    // ------------------------
    // 🔹 Strike Switch
    // -----------------------
    window.switchStrike = function () {
        fetch("{{ route('admin.cricket-matches.scoreboard.switch-strike') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                match_id: matchId
            })
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Failed to switch strike');

                // Update local state
                matchState.striker = {
                    id: data.data.striker.id,
                    name: data.data.striker.name,
                    runs: data.data.striker.runs ?? 0,
                    balls: data.data.striker.balls ?? 0
                };
                matchState.nonStriker = {
                    id: data.data.nonStriker.id,
                    name: data.data.nonStriker.name,
                    runs: data.data.nonStriker.runs ?? 0,
                    balls: data.data.nonStriker.balls ?? 0
                };

                // Update UI
                updatePlayerCard(matchState.striker, 'striker');
                updatePlayerCard(matchState.nonStriker, 'nonStriker');

                saveMatchState();
            })
            .catch(err => console.error("Error switching strike:", err));
    }

    function updateStrikeButtons() {
        document.querySelectorAll('.on-strike-btn').forEach(btn => btn.remove());

        const btn = document.createElement('button');
        btn.classList.add('btn', 'btn-info', 'btn-sm', 'on-strike-btn');
        btn.innerText = 'On Strike';
        btn.onclick = () => switchStrike();

        const nonStrikerContainer = document.getElementById('nonStrikerActions');
        if (nonStrikerContainer) nonStrikerContainer.appendChild(btn);
    }

    // Helper to update player UI
    function updatePlayerCard(player, type) {
        const cardId = type === 'striker' ? 'strikerName' : 'nonStrikerName';
        const runsId = type === 'striker' ? 'strikerRuns' : 'nonStrikerRuns';
        const ballsId = type === 'striker' ? 'strikerBallsFaced' : 'nonStrikerBallsFaced';
        const actionsId = type === 'striker' ? 'strikerActions' : 'nonStrikerActions';

        if (player) {
            document.getElementById(cardId).innerText = player.name;
            document.getElementById(runsId).innerText = player.runs ?? 0;
            document.getElementById(ballsId).innerText = player.balls ?? 0;

            if (type === 'nonStriker') {
                document.getElementById(actionsId).innerHTML = `
                            <button class="btn btn-danger btn-sm" onclick="switchStrike()">On Strike</button>
                        `;
            } else {
                document.getElementById(actionsId).innerHTML = ''; // remove button for striker
            }
        } else {
            document.getElementById(cardId).innerText = 'Choose Player';
            document.getElementById(runsId).innerText = '00';
            document.getElementById(ballsId).innerText = '0';
            document.getElementById(actionsId).innerHTML = '';
        }
    }

    // ------------------------
    // 🔹 Save match state to localStorage
    // ------------------------
    function saveMatchState() {
        localStorage.removeItem(stateKey);
        const state = {
            striker: strikerId ? {
                id: strikerId,
                name: document.getElementById("strikerName")?.innerText,
                runs: document.getElementById("strikerRuns")?.innerText ?? "0",
                balls: document.getElementById("strikerBallsFaced")?.innerText ?? "0",
            } : null,

            nonStriker: nonStrikerId ? {
                id: nonStrikerId,
                name: document.getElementById("nonStrikerName")?.innerText,
                runs: document.getElementById("nonStrikerRuns")?.innerText ?? "0",
                balls: document.getElementById("nonStrikerBallsFaced")?.innerText ?? "0",
            } : null,

            team: {
                name: document.getElementById("battingTeamName")?.innerText,
                score: document.getElementById("currentScore")?.innerText,
                overs: document.getElementById("currentOvers")?.innerText,
                crr: document.getElementById("currentCRR")?.innerText,
                projected: document.getElementById("projectedScore")?.innerText
            },

            currentBowler: parseInt($('input[name="current-bowler"]:checked').attr('data-playerid')) ||
                null,
        };

        localStorage.setItem(stateKey, JSON.stringify(state));
    }

    // ------------------------
    // 🔹 Load saved state from localStorage
    // ------------------------
    function loadFullMatchState(matchId) {
        fetch("{{ route('admin.cricket-matches.scoreboard.full-match-state', ['match_id' => ':match_id']) }}".replace(':match_id', matchId))
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    console.error("Failed to load match state:", data.message);
                    return;
                }

                // ------------------------
                // 🔹 Save match_state to localStorage & populate striker/non-striker/bowler
                // ------------------------
                localStorage.setItem(stateKey, JSON.stringify(data.match_state));
                populateMatchState(data.match_state);

                // ------------------------
                // 🔹 Match Result (completed)
                // ------------------------
                if (data.match_result) {
                    const showMatchResultAlert = () => {
                        Swal.fire({
                            title: `<span style="color:#155724;">🏆 ${data.match_result.winning_team}</span>`,
                            html: `<h4 style="margin-top:10px;">${data.match_result.summary}</h4>`,
                            icon: 'success',
                            background: '#f0f9f4',
                            color: '#155724',
                            confirmButtonColor: '#198754',
                            confirmButtonText: 'Celebrate 🎉',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showClass: {
                                popup: 'animate__animated animate__fadeInDown'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutUp'
                            }
                        }).then(() => {
                            if (typeof confetti === "function") {
                                confetti({
                                    particleCount: 200,
                                    spread: 100,
                                    origin: { y: 0.6 }
                                });
                            }
                            // re-show again until user closes tab
                            showMatchResultAlert();
                        });
                    };

                    showMatchResultAlert();
                    return; // stop rendering further since match ended
                }

                // ------------------------
                // 🔹 Current Innings Stats
                // ------------------------
                const currentInnings = data.innings[data.innings.length - 1];
                const batting = currentInnings.batting;
                const bowling = currentInnings.bowling;
                const scoreboard = currentInnings.scoreboard;
                const partnerships = currentInnings.partnerships;
                const fallOfWickets = currentInnings.fall_of_wickets;

                // Show/Hide target and RRR
                if (scoreboard.target && scoreboard.target > 0) {
                    $('#targetScore').text(scoreboard.target);
                    $('#requiredRunRate').text(scoreboard.requiredRR);
                    $('.tagetscore-container').removeClass('d-none');
                    $('.requiredRunRate-container').removeClass('d-none');
                } else {
                    $('.tagetscore-container').addClass('d-none');
                    $('.requiredRunRate-container').addClass('d-none');
                }

                $('input[name="innings"]').val(currentInnings.innings);
                $('#bowling_team_id').val(currentInnings.bowling_team_id);

                // ✅ Scoreboard update
                $('#currentScore').text(scoreboard.runs + " / " + scoreboard.wickets);
                $('#currentOvers').text(scoreboard.overs + " / " + scoreboard.totalOvers);
                $('#currentCRR').text(scoreboard.currentCRR);
                $('#projectedScore').text(scoreboard.projected);

                // ✅ Batting table
                const tbody = document.querySelector('#batting-stats');
                tbody.innerHTML = '';
                if (batting) {
                    batting.forEach(player => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                                    <td>${player.name}</td>
                                    <td class='text-center'>${player.runs}</td>
                                    <td class='text-center'>${player.balls}</td>
                                    <td class='text-center'>${player.fours}</td>
                                    <td class='text-center'>${player.sixes}</td>
                                    <td class='text-center'>${player.strike_rate}</td>
                                `;
                        tbody.appendChild(tr);
                    });
                }

                // ✅ Bowling table
                const state = JSON.parse(localStorage.getItem(stateKey) || "{}");
                const bowlerId = state?.currentBowler ?? null;

                const bowlingTbody = document.querySelector('#bowling-stats');
                bowlingTbody.innerHTML = '';
                if (bowling) {
                    bowling.forEach(player => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                                    <td style="vertical-align: middle;display: flex;align-items: center;">
                                        ${player.name}
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" value="${player.id}" data-playerid="${player.id}" name="current-bowler" style="margin-left: 10px;" ${bowlerId == player.id ? 'checked' : ''}>
                                        </div>
                                    </td>
                                    <td class='text-center'>${player.overs}</td>
                                    <td class='text-center'>${player.maidens ?? 0}</td>
                                    <td class='text-center'>${player.runs_conceded}</td>
                                    <td class='text-center'>${player.wickets}</td>
                                    <td class='text-center'>${player.economy_rate}</td>
                                `;
                        bowlingTbody.appendChild(tr);
                    });
                }

                // ✅ Bowler selection listener
                bowlingTbody.addEventListener('change', (e) => {
                    if (e.target.name === 'current-bowler') {
                        saveMatchState();
                    }
                });

                // ✅ Partnerships
                const partnershipList = document.querySelector('#partnership-stats');
                partnershipList.innerHTML = '';
                if (partnerships && partnerships.length > 0) {
                    partnerships.forEach(p => {
                        let trContent = `<tr>
                                    <th>
                                        <div class="d-flex align-items-center p-2">
                                            <img src="${p.batter1.img || ''}" alt="${p.batter1.name}" class="rounded-circle me-3" width="48" height="48" style="object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <h5 class="mb-2">${p.batter1.name}</h5>
                                                <h6 class="text-muted">${p.batter1.role}</h6>
                                            </div>
                                        </div>
                                    </th>
                                    <td class="text-center">
                                        <div class="mb-1">
                                            <small>${p.runs} (${p.balls} balls)</small>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar" role="progressbar" style="background: #F4991A!important;width: ${p.batter1.percent}%" aria-valuenow="${p.batter1.percent}" aria-valuemin="0" aria-valuemax="100"></div>
                                            <div class="progress-bar" role="progressbar" style="background: #84994F!important;width: ${p.batter2?.percent ?? 0}%" aria-valuenow="${p.batter2?.percent ?? 0}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <div class="d-flex align-items-center p-2">
                                            <div class="flex-grow-1 mr-2">
                                                <h5 class="mb-2">${p.batter2?.name ?? ''}</h5>
                                                <h6 class="text-muted">${p.batter2?.role ?? ''}</h6>
                                            </div>
                                            ${p.batter2 ? `<img src="${p.batter2.img || ''}" alt="${p.batter2.name}" class="rounded-circle" width="48" height="48" style="object-fit: cover; margin-left: 15px;">` : ''}
                                        </div>
                                    </td>
                                </tr>`;
                        partnershipList.innerHTML += trContent;
                    });
                } else {
                    partnershipList.innerHTML = `<tr>
                                <th colspan="3" class="text-center">Players Not Entered Yet</th>
                            </tr>`;
                }

                // ✅ Fall of wickets
                const fallWicketsList = document.querySelector('#fallofwickets-stats');
                fallWicketsList.innerHTML = '';
                if (fallOfWickets && fallOfWickets.length > 0) {
                    fallOfWickets.forEach(w => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                                    <th>${w.player_name}</th>
                                    <td class="text-center">${w.runs}-${w.wicket_number}</td>
                                    <td class="text-center">${w.over}</td>
                                `;
                        fallWicketsList.appendChild(tr);
                    });
                } else {
                    fallWicketsList.innerHTML = `<tr>
                                <th colspan="3" class="text-center">No wickets fallen yet</th>
                            </tr>`;
                }
            })
            .catch(err => console.error("Error fetching full match state:", err));
    }

    // function loadMatchState() {
    //     fetch("{{ route('admin.cricket-matches.scoreboard.match-info', ['match_id' => ':match_id']) }}".replace(':match_id', matchId))
    //         .then(res => res.json())
    //         .then(data => {
    //             if (data.success) {
    //                 localStorage.setItem(stateKey, JSON.stringify(data.match_state));
    //                 populateMatchState(data.match_state);
    //             } else {
    //                 console.error("Failed to load match state:", data.message);
    //             }
    //         })
    //         .catch(err => console.error("Error fetching match state:", err));
    // }

    function populateMatchState(state) {
        // Striker
        if (state.striker) {
            strikerId = state.striker.id;
            $('.btn-batsman-out.striker-btn').attr('data-batsman', strikerId);
            document.getElementById("strikerName").innerText = state.striker.name;
            document.getElementById("strikerRuns").innerText = state.striker.runs;
            document.getElementById("strikerBallsFaced").innerText = state.striker.balls;
        }

        // Non-striker
        if (state.nonStriker) {
            nonStrikerId = state.nonStriker.id;
            $('.btn-batsman-out.nonstriker-btn').attr('data-batsman', nonStrikerId);
            document.getElementById("nonStrikerName").innerText = state.nonStriker.name;
            document.getElementById("nonStrikerRuns").innerText = state.nonStriker.runs;
            document.getElementById("nonStrikerBallsFaced").innerText = state.nonStriker.balls;

            document.getElementById("nonStrikerActions").innerHTML = `
                        <button class="btn btn-danger btn-sm" onclick="switchStrike()">On Strike</button>
                    `;
        } else {
            document.getElementById("nonStrikerActions").innerHTML = '';
        }

        // Team info
        if (state.team) {
            document.getElementById("battingTeamName").innerText = state.team.name;
            document.getElementById("currentScore").innerText = state.team.score + " / " + (state.team
                .wickets || 0);
            document.getElementById("currentOvers").innerText = state.team.overs;
            document.getElementById("currentCRR").innerText = state.team.crr;
            document.getElementById("projectedScore").innerText = state.team.projected;
        }

        // Current bowler
        if (state.currentBowler) {
            const bowlerId = parseInt(state.currentBowler);
            $('input[name="current-bowler"]').each(function () {
                const pid = parseInt($(this).attr('data-playerid'));
                $(this).prop('checked', pid === bowlerId);
            });
        }
    }

    function loadCurrentPlayersToModal() {
        const state = JSON.parse(localStorage.getItem(stateKey) || "{}");

        const players = [];
        if (state.striker) players.push(state.striker);
        if (state.nonStriker) players.push(state.nonStriker);

        const container = $('#nbBatsmanOut .player-wrapper');
        container.empty(); // Clear old entries

        players.forEach(player => {
            const playerCard = `
                        <div class="input-card">
                            <input class="input player-id" type="radio" name="player_id" value="${player.id}">
                            <span class="check"></span>
                            <label class="label">
                                <div class="title">${player.name}</div>
                                <div class="score">Score: ${player.score}</div>
                            </label>
                        </div>`;
            container.append(playerCard);
        });

        // Show the modal section if hidden
        container.removeClass('d-none');
    }

    // ------------------------
    // 🔹 Toss Selection
    // ------------------------
    $('input[name="toss-team"]').on('change', function () {
        selectedTeam = $(this).val();
        submitTossIfReady();
    });

    $('input[name="toss-decision"]').on('change', function () {
        selectedDecision = $(this).val();
        submitTossIfReady();
    });

    function submitTossIfReady() {
        if (selectedTeam && selectedDecision) {
            $.ajax({
                url: "{{ route('admin.cricket-matches.toss.store') }}",
                method: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    match_id: matchId,
                    toss_winner_team_id: selectedTeam,
                    toss_decision: selectedDecision
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });

                        // ✅ Use batting_team_name instead of toss_winner_team_name
                        $('#battingTeamName').text(response.batting_team_name);

                        saveMatchState();
                    } else {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: response.message || 'Something went wrong',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: xhr.responseJSON.message || 'Something went wrong',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                }
            });
        }
    }

    // ------------------------
    // 🔹 Select Batsman
    // ------------------------
    window.selectBatsman = function (playerId) {
        const matchState = JSON.parse(localStorage.getItem(stateKey) || "{}");
        const battingTeamId = matchState.battingTeamId;

        let role = null;

        // Determine role based on which player slot is empty
        if (!matchState.striker) {
            role = 'on-strike';
        } else if (!matchState.nonStriker) {
            role = 'batting';
        } else {
            Swal.fire('Both striker and non-striker are already selected.');
            return;
        }

        fetch("{{ route('admin.cricket-matches.scoreboard.select-batsman') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                match_id: matchId,
                team_id: battingTeamId,
                player_id: playerId,
                role: role
            })
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) throw new Error(data.message);

                const player = data.data.match_player;

                if (role === 'on-strike') {
                    matchState.striker = {
                        id: player.player_id,
                        name: player.player?.user?.full_name || player.full_name,
                        runs: 0,
                        balls: 0
                    };
                } else if (role === 'batting') {
                    matchState.nonStriker = {
                        id: player.player_id,
                        name: player.player?.user?.full_name || player.full_name,
                        runs: 0,
                        balls: 0
                    };
                }

                localStorage.setItem(stateKey, JSON.stringify(matchState));

                // Update UI
                if (role === 'on-strike') {
                    document.getElementById("strikerName").innerText = matchState.striker.name;
                    document.getElementById("strikerRuns").innerText = "00";
                    document.getElementById("strikerBallsFaced").innerText = "0";
                } else {
                    document.getElementById("nonStrikerName").innerText = matchState.nonStriker
                        .name;
                    document.getElementById("nonStrikerRuns").innerText = "00";
                    document.getElementById("nonStrikerBallsFaced").innerText = "0";
                }

                // Remove player from Yet-To-Bat list
                const card = document.querySelector(`[data-player-id="${playerId}"]`);
                if (card) card.remove();
                window.location.reload();
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', err.message, 'error');
            });
    }

    function saveBatsman(matchId, teamId, playerId, status, callback) {
        fetch("{{ route('admin.cricket-matches.scoreboard.select-batsman') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                match_id: matchId,
                team_id: teamId,
                player_id: playerId,
                role: status
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || 'Player saved successfully',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });

                    if (callback) callback();
                } else {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: data.message || 'Failed to save player',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                    console.error("Failed to save player:", data);
                }
            })
            .catch(err => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Error saving player',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true
                });
                console.error("Error saving player:", err);
            });
    }

    // ------------------------
    // 🔹 Select Bowler
    // ------------------------
    window.selectBowler = function (playerId) {
        const state = JSON.parse(localStorage.getItem(stateKey) || "{}");
        state.currentBowler = playerId;
        localStorage.setItem(stateKey, JSON.stringify(state));
        fetch("{{ route('admin.cricket-matches.scoreboard.select-bowler') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                match_id: matchId,
                team_id: state.battingTeamId,
                player_id: playerId,
            })
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) throw new Error(data.message);
                window.location.reload();
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', err.message, 'error');
            });
    }

    // Use delegation from the table body (or any static parent)
    $('#bowling-stats').on('change', 'input[name="current-bowler"]', function () {
        const playerId = $(this).data('playerid');
        selectBowler(playerId);
    });

    // ------------------------
    // Add a delivery
    // ------------------------
    function addDelivery({
        runs = 0,
        extra = null,
        wicket = null,
        batsmanOut = null,
        legalBall = true
    }) {
        const state = JSON.parse(localStorage.getItem(stateKey) || "{}");
        const strikerId = state?.striker?.id ?? null;
        const nonStrikerId = state?.nonStriker?.id ?? null;

        let bowlerId = state?.currentBowler ?? null;
        if (!bowlerId) {
            const selectedBowlerInput = document.querySelector('input[name="current-bowler"]:checked');
            bowlerId = selectedBowlerInput ? selectedBowlerInput.dataset.playerid : null;
        }

        if (!bowlerId) {
            Swal.fire('Error', 'Please select the current bowler before recording delivery.', 'error');
            return;
        }

        const payload = {
            match_id: matchId,
            striker_id: strikerId ? Number(strikerId) : null,
            non_striker_id: nonStrikerId ? Number(nonStrikerId) : null,
            bowler_id: Number(bowlerId),
            runs: Number(runs ?? 0),
            extras: extra ? extra : [],
            wicket: wicket || null,
            batsman_out: batsmanOut || null,
            legal_ball: legalBall
        };

        sendDeliveryToServer(payload);
    }

    // ------------------------
    // Runs buttons
    // ------------------------
    document.querySelectorAll('.btn-run').forEach(btn => {
        btn.addEventListener('click', e => {
            const run = parseInt(e.target.dataset.run);

            addDelivery({
                runs: run
            });
        });
    });

    // ------------------------
    // Extras buttons
    // ------------------------
    document.querySelectorAll('.btn-extra').forEach(btn => {
        btn.addEventListener('click', () => {
            const extraModal = document.getElementById('extraModal');
            if (!extraModal) {
                console.error("#extraModal not found in DOM");
                return;
            }

            const modalTitle = extraModal.querySelector('.modal-title');
            const nbSection = document.getElementById('nbSection');
            const wdSection = document.getElementById('wdSection');
            const lbSection = document.getElementById('lbSection');
            const nbRunOutCheckbox = document.getElementById('nbRunOut');
            const nbBatsmanOut = document.getElementById('nbBatsmanOut');
            const type = btn.dataset.extra;

            // 🔹 Reset sections
            nbSection.classList.add('d-none');
            wdSection.classList.add('d-none');
            lbSection.classList.add('d-none');
            modalTitle.textContent = "Extra";

            if (type === "NB") {
                modalTitle.textContent = "No Ball";
                nbSection.classList.remove('d-none');

                // Run Out toggle
                nbRunOutCheckbox.addEventListener('change', function () {
                    if (this.checked) {
                        nbBatsmanOut.classList.remove('d-none');
                    } else {
                        nbBatsmanOut.classList.add('d-none');
                    }
                });

                loadCurrentPlayersToModal();
            } else if (type === "WD") {
                modalTitle.textContent = "Wide Ball";
                wdSection.classList.remove('d-none');
            } else if (type === "LB") {
                modalTitle.textContent = "Leg Bye";
                lbSection.classList.remove('d-none');
            }
        });
    });

    // ------------------------
    // Extra modal: submit runs
    // ------------------------
    document.getElementById("extraForm").addEventListener("submit", function (e) {
        e.preventDefault(); // Prevent form submission

        // Determine which type of extra is selected
        let extra = null;
        let batsmanOut = null;

        // No Ball
        const nbSection = document.getElementById("nbSection");
        if (!nbSection.classList.contains("d-none")) {
            const nbRun = document.querySelector('input[name="nbRuns"]:checked');
            const runOutChecked = document.getElementById("nbRunOut").checked;
            extra = {
                type: "NB",
                runs: nbRun ? Number(nbRun.value) : 0,
                run_out: runOutChecked
            };

            if (runOutChecked) {
                const selectedBatsman = nbSection.querySelector('input[name="player_id"]:checked');
                if (selectedBatsman) batsmanOut = selectedBatsman.value;
            }
        }

        // Wide Ball
        const wdSection = document.getElementById("wdSection");
        if (!wdSection.classList.contains("d-none")) {
            const wdRun = document.querySelector('input[name="wdExtraRuns"]:checked');
            extra = {
                type: "WD",
                runs: wdRun ? Number(wdRun.value) : 1,
                run_out: false
            };
        }

        // Leg Bye
        const lbSection = document.getElementById("lbSection");
        if (!lbSection.classList.contains("d-none")) {
            const lbRun = document.querySelector('input[name="lbRuns"]:checked');
            extra = {
                type: "LB",
                runs: lbRun ? Number(lbRun.value) : 0,
                run_out: false
            };
        }

        // Now call addDelivery with extra
        addDelivery({
            runs: 0, // runs scored by batsman
            extra: extra,
            wicket: extra?.run_out ? "run_out" : null,
            batsmanOut: batsmanOut,
            legalBall: false // extras are generally illegal deliveries
        });

        // Close modal after submission
        const extraModal = bootstrap.Modal.getInstance(document.getElementById("extraModal"));
        extraModal.hide();
    });

    // ------------------------
    // Wicket buttons
    // ------------------------
    document.querySelectorAll('.btn-wicket').forEach(btn => {
        btn.addEventListener('click', e => {
            const wicketType = e.target.dataset.wicket;
            currentWicket = wicketType;

            const modal = new bootstrap.Modal(document.getElementById('wicketModal'));

            // Show run-out options only for Run Out
            document.getElementById('runOutOptions').classList.toggle('d-none',
                wicketType !== 'Run Out');

            // Load players for caught/stumped
            if (wicketType === 'Caught') loadBowlingTeamPlayers('caughtBySelect');
            if (wicketType === 'Stumped') loadBowlingTeamPlayers('stumpedBySelect');

            modal.show();
        });
    });

    document.querySelectorAll(".btn-wicket-type").forEach(btn => {
        btn.addEventListener("click", function () {
            const type = this.dataset.wicket;

            // Hide all extra sections first
            document.querySelectorAll(".wicket-extra").forEach(el => el.classList.add(
                "d-none"));

            if (type === "Run Out") {
                document.getElementById("runOutOptions").classList.remove("d-none");
            } else if (type === "Caught") {
                document.getElementById("caughtOptions").classList.remove("d-none");
            } else if (type === "Stumped") {
                document.getElementById("stumpedOptions").classList.remove("d-none");
            } else {
                finalizeWicket({
                    type: type,
                    batsmanOut: $('.btn-batsman-out.striker-btn').attr(
                        'data-batsman')
                });
            }
        });
    });

    // Run Out buttons
    document.querySelectorAll('.btn-batsman-out').forEach(btn => {
        btn.addEventListener('click', () => {
            const batsmanOutId = Number(btn.dataset.batsman);
            finalizeWicket({
                type: 'Run Out',
                batsmanOut: batsmanOut
            });
        });
    });

    function finalizeCaughtOrStumped(type) {
        let batsmanOut = 'Striker';
        let fielderId = null;
        if (type === 'Caught') {
            fielderId = document.getElementById('caughtBySelect').value;
        } else if (type === 'Stumped') {
            fielderId = document.getElementById('stumpedBySelect').value;
        }

        finalizeWicket({
            type: type,
            batsmanOut: batsmanOut,
            fielderId: fielderId
        });
    }

    function finalizeWicket({
        type,
        batsmanOut,
        fielderId = null
    }) {
        const wicketModalEl = document.getElementById('wicketModal');
        let modalInstance = bootstrap.Modal.getInstance(wicketModalEl);
        if (!modalInstance) {
            modalInstance = new bootstrap.Modal(wicketModalEl);
        }

        modalInstance.hide();
        setTimeout(() => {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open'); // remove modal-open class if stuck
        }, 100);

        let extra = null;
        if (type === 'Run Out') extra = {
            run_out: true
        };
        else if (type === 'Caught') extra = {
            caught_by: fielderId
        };
        else if (type === 'Stumped') extra = {
            stumped_by: fielderId
        };

        // Add delivery
        addDelivery({
            runs: 0,
            extra: extra,
            wicket: type,
            batsmanOut: batsmanOut,
            legalBall: true
        });
    }

    $('#btn-reset-wicket-form').on('click', function () {
        document.querySelectorAll(".btn-wicket-type").forEach(btn => {
            btn.classList.remove("active");
            btn.classList.remove("d-none"); // show them again if previously hidden
        });

        document.querySelectorAll(".wicket-extra").forEach(el => {
            el.classList.add("d-none");
        });

        $('#wicketModal select').val('').trigger('change'); // reset dropdowns
        $('#wicketModal input').val(''); // reset inputs
        window.currentWicketData = null;
    });

    // Example function to populate dropdown from bowling team
    function loadBowlingTeamPlayers(selectId) {
        const select = document.getElementById(selectId);
        select.innerHTML = ""; // clear previous
        if (!bowlingTeamPlayers.length) {
            select.innerHTML = `<option value="">No players available</option>`;
            return;
        }
        bowlingTeamPlayers.forEach(player => {
            const opt = document.createElement("option");
            opt.value = player.id;
            opt.textContent = player.name;
            select.appendChild(opt);
        });
    }

    // ------------------------
    // Send payload to backend
    // ------------------------
    function sendDeliveryToServer(delivery) {
        fetch("{{ route('admin.cricket-matches.scoreboard.store-delivery') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(delivery)
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Failed to record delivery');

                const saved = JSON.parse(localStorage.getItem(stateKey) || "{}");
                saved.striker = data.updated_state.striker;
                saved.nonStriker = data.updated_state.nonStriker;
                localStorage.setItem(stateKey, JSON.stringify(saved));

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message || 'Delivery stored & stats updated.',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true
                }).then(() => {
                    loadFullMatchState(matchId);
                    loadCurrentOver();
                });

            })
            .catch(err => console.error("Error recording delivery:", err));
    }

    function loadCurrentOver() {
        let chooseBowlerRoute =
            "{{ route('admin.cricket-matches.scoreboard.current-over', ['match' => '__MATCH__']) }}";
        const url = chooseBowlerRoute.replace('__MATCH__', matchId);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return console.error(data.message);

                const container = document.getElementById('currentOverDetails');
                container.innerHTML = '';

                data.balls.forEach(ball => {
                    const span = document.createElement('span');

                    // Add multiple classes safely
                    if (ball.class) {
                        ball.class.split(' ').forEach(cls => {
                            if (cls.trim()) span.classList.add(cls.trim());
                        });
                    }

                    // Ensure ball.ball is a string
                    const ballLabel = String(ball.ball);

                    // Highlight wicket with red color
                    if (ballLabel.includes('W')) {
                        span.classList.add('wicket-ball'); // define in CSS
                    }

                    span.innerText = ballLabel;
                    container.appendChild(span);
                });
            })
            .catch(err => console.error('Error loading current over:', err));
    }

    // ------------------------
    // 🔹 Load Yet-To-Bat Players
    // ------------------------
    function loadYetToBatPlayers() {
        fetch('/api/matches/yet-to-bat/' + matchId)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                matchState.battingTeamId = data.battingTeamId;

                const list = document.getElementById('yetToBatList');
                list.innerHTML = '';

                if (!data.players.length) {
                    list.innerHTML = '<li class="list-group-item text-muted">All players batted</li>';
                    return;
                }

                data.players.forEach(player => {
                    const card = document.createElement('div');
                    card.className = 'card mb-2 player-card';
                    card.dataset.playerId = player.id;
                    card.dataset.playerName = player.full_name;
                    card.innerHTML = `
                                <div class="align-items-center p-2 border flt-attribute" 
                                    style="display: flex;">
                                    <img src="${player.image}" alt="${player.full_name}" 
                                        class="rounded-circle me-3" width="48" height="48" 
                                        style="object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">${player.short_name}</h6>
                                        <small class="text-muted">${player.role}</small>
                                    </div>
                                    <button class="btn btn-sm btn-primary select-player-btn">Select</button>
                                </div>
                            `;
                    list.appendChild(card);
                });
            });
    }

    // ------------------------
    // 🔹 Select Players From Yet To Bat
    // ------------------------
    document.getElementById('yetToBatList').addEventListener('click', function (e) {
        if (!e.target.classList.contains('select-player-btn')) return;
        const card = e.target.closest('.player-card');
        const playerId = card.dataset.playerId;
        selectBatsman(playerId);
    });

    // ------------------------
    // 🔹 Search Filter
    // ------------------------
    $('#flt-player').on('input', function () {
        const searchTerm = this.value.toLowerCase();
        const cards = document.querySelectorAll('#yetToBatList .player-card');

        cards.forEach(card => {
            const playerName = card.querySelector('.flt-attribute')
                .getAttribute('data-player-name')
                .toLowerCase();
            card.style.display = playerName.includes(searchTerm) ? 'flex' : 'none';
        });
    });

    // ------------------------
    // Select Bowler
    // ------------------------
    const fetchBowlingTeamPlayers = () => {
        $.ajax({
            url: `/admin/cricket-matches/scoreboard/${matchId}/team-b-players`,
            type: "GET",
            success: function (players) {
                const options = players.map(p => ({
                    id: p.id,
                    text: `${p.name} - ${p.style}`
                }));

                $('#bowler-select').select2({
                    data: options,
                    placeholder: "Select a bowler",
                    width: "100%"
                });
            }
        });
    }

    // When a bowler is selected
    $('#bowler-select').on('select2:select', function (e) {
        const bowlerId = e.params.data.id;
        const teamId = $('#bowling_team_id').val();
        let chooseBowlerRoute =
            "{{ route('admin.cricket-matches.scoreboard.choose-bowler', ['match' => '__MATCH__']) }}";
        const url = chooseBowlerRoute.replace('__MATCH__', matchId);
        $.ajax({
            url: url,
            type: "POST",
            contentType: "application/json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify({
                match_id: matchId,
                bowler_id: bowlerId,
                team_id: teamId
            }),
            success: function (res) {
                if (res.success) {
                    const bowlingTbody = document.querySelector('#bowling-stats');
                    bowlingTbody.innerHTML = ""; // Clear all previous rows

                    // Append updated bowling stats
                    res.bowling.forEach(player => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                                    <td>${player.name} - <small>${player.style}</small></td>
                                    <td class="text-center">${player.overs}</td>
                                    <td class="text-center">${player.maidens ?? 0}</td>
                                    <td class="text-center">${player.runs_conceded}</td>
                                    <td class="text-center">${player.wickets}</td>
                                    <td class="text-center">${player.economy_rate}</td>
                                `;
                        bowlingTbody.appendChild(tr);
                    });

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: "Bowler Selected..",
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                } else {
                    alert(res.message || "Something went wrong!");
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert("Error saving bowler.");
            }
        });
    });

    function updateBowlingTable(bowlingList) {
        const tbody = document.querySelector('#bowling-stats');
        tbody.innerHTML = '';
        bowlingList.forEach(player => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                        <td>${player.name}</td>
                        <td>${player.overs}</td>
                        <td>${player.runs_conceded}</td>
                        <td>${player.wickets}</td>
                        <td>${player.economy_rate}</td>
                    `;
            tbody.appendChild(tr);
        });
    }

    // ------------------------
    // Load Current Statistics
    // ------------------------
    function loadCurrentStats(matchId) {
        fetch("{{ route('admin.cricket-matches.scoreboard.load-current-stats', ['match_id' => ':match_id']) }}".replace(':match_id', matchId))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    console.log(data.match_result)
                    if (data.match_result) {
                        // Show alert repeatedly until dismissed
                        const showMatchResultAlert = () => {
                            Swal.fire({
                                title: `<span style="color:#155724;">🏆 ${data.match_result.winning_team}</span>`,
                                html: `<h4 style="margin-top:10px;">${data.match_result.summary}</h4>`,
                                icon: 'success',
                                background: '#f0f9f4',
                                color: '#155724',
                                confirmButtonColor: '#198754',
                                confirmButtonText: 'Celebrate 🎉',
                                allowOutsideClick: false, // prevent closing by clicking outside
                                allowEscapeKey: false, // prevent closing with ESC
                                showClass: {
                                    popup: 'animate__animated animate__fadeInDown'
                                },
                                hideClass: {
                                    popup: 'animate__animated animate__fadeOutUp'
                                }
                            }).then(() => {
                                // 💥 Confetti effect
                                if (typeof confetti === "function") {
                                    confetti({
                                        particleCount: 200,
                                        spread: 100,
                                        origin: {
                                            y: 0.6
                                        }
                                    });
                                }
                                // After closing, re-show the alert
                                showMatchResultAlert();
                            });
                        };

                        // Initial call
                        showMatchResultAlert();

                        return; // stop rendering stats if match already ended
                    }

                    const currentInnings = data.innings[data.innings.length - 1];
                    const batting = currentInnings.batting;
                    const bowling = currentInnings.bowling;
                    const scoreboard = currentInnings.scoreboard;
                    const partnerships = currentInnings.partnerships;
                    const fallOfWickets = currentInnings.fall_of_wickets;

                    if (data.isMatchEnded) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Match Ended',
                            text: 'All innings are complete.',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    if (scoreboard.target && scoreboard.target > 0) {
                        $('#targetScore').text(scoreboard.target);
                        $('#requiredRunRate').text(scoreboard.requiredRR);
                        $('.tagetscore-container').removeClass('d-none');
                        $('.requiredRunRate-container').removeClass('d-none');
                    } else {
                        $('.tagetscore-container').addClass('d-none');
                        $('.requiredRunRate-container').addClass('d-none');
                    }

                    $('input[name="innings"]').val(currentInnings.innings);
                    $('#bowling_team_id').val(currentInnings.bowling_team_id);

                    // ✅ Scoreboard update
                    $('#currentScore').text(scoreboard.runs + " / " + scoreboard.wickets);
                    $('#currentOvers').text(scoreboard.overs + " / " + scoreboard.totalOvers);
                    $('#currentCRR').text(scoreboard.currentCRR);
                    $('#projectedScore').text(scoreboard.projected);

                    // ✅ Batting table
                    const tbody = document.querySelector('#batting-stats');
                    tbody.innerHTML = '';
                    if (batting) {
                        batting.forEach(player => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                        <td>${player.name}</td>
                                        <td class='text-center'>${player.runs}</td>
                                        <td class='text-center'>${player.balls}</td>
                                        <td class='text-center'>${player.fours}</td>
                                        <td class='text-center'>${player.sixes}</td>
                                        <td class='text-center'>${player.strike_rate}</td>
                                    `;
                            tbody.appendChild(tr);
                        });
                    }

                    // ✅ Bowling table
                    const state = JSON.parse(localStorage.getItem(stateKey) || "{}");
                    const bowlerId = state?.currentBowler ?? null;

                    const bowlingTbody = document.querySelector('#bowling-stats');
                    bowlingTbody.innerHTML = '';
                    if (bowling) {
                        bowling.forEach(player => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                        <td style="vertical-align: middle;display: flex;align-items: center;">
                                            ${player.name}
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" value="${player.id}" data-playerid="${player.id}" name="current-bowler" style="margin-left: 10px;" ${bowlerId == player.id ? 'checked' : ''}>
                                            </div>
                                        </td>
                                        <td class='text-center'>${player.overs}</td>
                                        <td class='text-center'>${player.maidens ?? 0}</td>
                                        <td class='text-center'>${player.runs_conceded}</td>
                                        <td class='text-center'>${player.wickets}</td>
                                        <td class='text-center'>${player.economy_rate}</td>
                                    `;
                            bowlingTbody.appendChild(tr);
                        });
                    }

                    // ✅ Bowler selection listener
                    bowlingTbody.addEventListener('change', (e) => {
                        if (e.target.name === 'current-bowler') {
                            const currentBowlerId = e.target.value;
                            saveMatchState();
                        }
                    });

                    // ✅ Partnerships
                    const partnershipList = document.querySelector('#partnership-stats');
                    partnershipList.innerHTML = '';
                    if (partnerships && partnerships.length > 0) {
                        partnerships.forEach(p => {
                            let trContent = `<tr>
                                        <th>
                                            <div class="d-flex align-items-center p-2">
                                                <img src="${p.batter1.img || ''}" alt="${p.batter1.name}" class="rounded-circle me-3" width="48" height="48" style="object-fit: cover;">
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-2">${p.batter1.name}</h5>
                                                    <h6 class="text-muted">${p.batter1.role}</h6>
                                                </div>
                                            </div>
                                        </th>
                                        <td class="text-center">
                                            <div class="mb-1">
                                                <small>${p.runs} (${p.balls} balls)</small>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar" role="progressbar" style="background: #F4991A!important;width: ${p.batter1.percent}%" aria-valuenow="${p.batter1.percent}" aria-valuemin="0" aria-valuemax="100"></div>
                                                <div class="progress-bar" role="progressbar" style="background: #84994F!important;width: ${p.batter2?.percent ?? 0}%" aria-valuenow="${p.batter2?.percent ?? 0}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <div class="d-flex align-items-center p-2">
                                                <div class="flex-grow-1 mr-2">
                                                    <h5 class="mb-2">${p.batter2?.name ?? ''}</h5>
                                                    <h6 class="text-muted">${p.batter2?.role ?? ''}</h6>
                                                </div>
                                                ${p.batter2 ? `<img src="${p.batter2.img || ''}" alt="${p.batter2.name}" class="rounded-circle" width="48" height="48" style="object-fit: cover; margin-left: 15px;">` : ''}
                                            </div>
                                        </td>
                                    </tr>`;
                            partnershipList.innerHTML += trContent;
                        });
                    } else {
                        partnershipList.innerHTML = `<tr>
                                    <th colspan="3" class="text-center">Players Not Entered Yet</th>
                                </tr>`;
                    }

                    // ✅ Fall of wickets
                    const fallWicketsList = document.querySelector('#fallofwickets-stats');
                    fallWicketsList.innerHTML = '';
                    if (fallOfWickets && fallOfWickets.length > 0) {
                        fallOfWickets.forEach(w => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                        <th>${w.player_name}</th>
                                        <td class="text-center">${w.runs}-${w.wicket_number}</td>
                                        <td class="text-center">${w.over}</td>
                                    `;
                            fallWicketsList.appendChild(tr);
                        });
                    } else {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<th colspan="3" class="text-center">No wickets fallen yet</th>`;
                        fallWicketsList.appendChild(tr);
                    }

                    loadMatchState();
                } else {
                    console.error('Failed to load stats:', data.message);
                }
            })
            .catch(err => console.error('Error fetching stats:', err));
    }


    $('.btn-complete-innings').on('click', function () {
        $.get("{{ route('admin.cricket-matches.scoreboard.mark-innings-complete') }}", {
            match_id: matchId
        })
            .done(function (data) {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: data.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    loadFullMatchState(matchId);
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .fail(function () {
                Swal.fire('Error', 'Something went wrong', 'error');
            });
    });

    // ------------------------
    // 🔹 Init
    // ------------------------
    loadYetToBatPlayers();
    // loadMatchState();
    // loadCurrentStats(matchId);
    loadFullMatchState(matchId);
    fetchBowlingTeamPlayers();
    loadCurrentOver();
});