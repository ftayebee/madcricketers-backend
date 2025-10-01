$(document).ready(function () {
    // ------------------------
    // 🔹 Global State & Cache
    // ------------------------
    const matchId = $('input[name="toss_match_id"]').val();
    const stateKey = `match_state_${matchId}`;
    let selectedDecision = null;

    let matchState = {
        striker: null,
        nonStriker: null,
        battingTeamId: null,
        currentBowler: null
    };

    const $elements = {
        strikerName: $('#strikerName'),
        strikerRuns: $('#strikerRuns'),
        strikerBalls: $('#strikerBallsFaced'),
        nonStrikerName: $('#nonStrikerName'),
        nonStrikerRuns: $('#nonStrikerRuns'),
        nonStrikerBalls: $('#nonStrikerBallsFaced'),
        strikerActions: $('#strikerActions'),
        nonStrikerActions: $('#nonStrikerActions'),
        battingTeamName: $('#battingTeamName'),
        currentScore: $('#currentScore'),
        currentOvers: $('#currentOvers'),
        currentCRR: $('#currentCRR'),
        projectedScore: $('#projectedScore'),
        battingTbody: $('#batting-stats'),
        bowlingTbody: $('#bowling-stats'),
        partnershipTbody: $('#partnership-stats'),
        fallWicketsTbody: $('#fallofwickets-stats'),
        targetScore: $('#targetScore'),
        requiredRunRate: $('#requiredRunRate')
    };

    // ------------------------
    // 🔹 Match State Helpers
    // ------------------------
    const getMatchState = () => JSON.parse(localStorage.getItem(stateKey) || JSON.stringify(matchState));
    const setMatchState = (state) => {
        matchState = {
            ...getMatchState(),
            ...state
        };
        localStorage.setItem(stateKey, JSON.stringify(matchState));
    };

    // ------------------------
    // 🔹 AJAX Helpers
    // ------------------------
    const fetchJSON = (url, options = {}) => fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        ...options
    }).then(res => res.json());

    const showToast = (message, icon = 'success', timer = 2500) => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon,
            title: message,
            showConfirmButton: false,
            timer,
            timerProgressBar: true
        });
    }

    // ------------------------
    // 🔹 Update UI Functions
    // ------------------------
    document.getElementById('match-scoreboard').style.display = 'none';
    document.getElementById('match-result').style.display = 'none';

    const updatePlayerCard = (player, type) => {
        if (!player) {
            $elements[`${type}Name`].text('Choose Player');
            $elements[`${type}Runs`].text('00');
            $elements[`${type}Balls`].text('0');
            $elements[`${type}Actions`].html('');
            return;
        }

        $elements[`${type}Name`].text(player.name);
        $elements[`${type}Runs`].text(player.runs ?? 0);
        $elements[`${type}Balls`].text(player.balls ?? 0);

        if (type === 'nonStriker') {
            $elements.nonStrikerActions.html(
                `<button class="btn btn-danger btn-sm" id="switchStrikeBtn">On Strike</button>`);
        } else $elements.strikerActions.html('');
    };

    const renderTable = (tbody, rows) => {
        tbody.empty();
        if (!rows || !rows.length) return;
        const fragment = document.createDocumentFragment();
        rows.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = row;
            fragment.appendChild(tr);
        });
        tbody[0].appendChild(fragment);
    };

    const renderBattingTable = (batting) => {
        $elements.battingTbody.empty();
        if (!batting || !batting.length) return;
        const fragment = document.createDocumentFragment();
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
            fragment.appendChild(tr);
        });
        $elements.battingTbody[0].appendChild(fragment);
    };

    const renderBowlingTable = (bowling) => {
        $elements.bowlingTbody.empty();
        if (!bowling || !bowling.length) return;
        const state = getMatchState();
        const bowlerId = state?.currentBowler ?? null;

        const fragment = document.createDocumentFragment();
        bowling.forEach(player => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                        <td style="vertical-align: middle;display: flex;align-items: center;">
                            ${player.name}
                            <div class="form-check">
                                <input class="form-check-input" type="radio" data-playerid="${player.id}" name="current-bowler" style="margin-left: 10px;" ${bowlerId == player.id ? 'checked' : ''}>
                            </div>
                        </td>
                        <td class='text-center'>${player.overs}</td>
                        <td class='text-center'>${player.maidens ?? 0}</td>
                        <td class='text-center'>${player.runs_conceded}</td>
                        <td class='text-center'>${player.wickets}</td>
                        <td class='text-center'>${player.economy_rate}</td>
                    `;
            fragment.appendChild(tr);
        });
        $elements.bowlingTbody[0].appendChild(fragment);

        $elements.bowlingTbody.find('input[name="current-bowler"]').on('change', function () {
            sendBowlerData(matchId, $('#bowling_team_id').val(), $(this).data('playerid'));
        });
    };

    const renderPartnerships = (partnerships) => {
        $elements.partnershipTbody.empty();
        if (!partnerships || !partnerships.length) {
            $elements.partnershipTbody.html(
                '<tr><th colspan="3" class="text-center">Players Not Entered Yet</th></tr>');
            return;
        }

        const fragment = document.createDocumentFragment();
        partnerships.forEach(p => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
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
                            <div class="mb-1"><small>${p.runs} (${p.balls} balls)</small></div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" style="background: #F4991A!important;width: ${p.batter1.percent}%"></div>
                                <div class="progress-bar" style="background: #84994F!important;width: ${p.batter2?.percent ?? 0}%"></div>
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
                    `;
            fragment.appendChild(tr);
        });
        $elements.partnershipTbody[0].appendChild(fragment);
    };

    const renderFallOfWickets = (fallOfWickets) => {
        $elements.fallWicketsTbody.empty();
        if (!fallOfWickets || !fallOfWickets.length) {
            $elements.fallWicketsTbody.html(
                '<tr><th colspan="3" class="text-center">No wickets fallen yet</th></tr>');
            return;
        }

        const fragment = document.createDocumentFragment();
        fallOfWickets.forEach(w => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                        <th>${w.player_name}</th>
                        <td class="text-center">${w.runs}-${w.wicket_number}</td>
                        <td class="text-center">${w.over}</td>
                    `;
            fragment.appendChild(tr);
        });
        $elements.fallWicketsTbody[0].appendChild(fragment);
    };

    const updateScoreboard = (scoreboard) => {
        $elements.currentScore.text(`${scoreboard.runs} / ${scoreboard.wickets}`);
        $elements.currentOvers.text(`${scoreboard.overs} / ${scoreboard.totalOvers}`);
        $elements.currentCRR.text(`(${scoreboard.currentCRR})`);

        if (scoreboard.target && scoreboard.target > 0) {
            $elements.targetScore.text(scoreboard.target);
            $elements.requiredRunRate.text(scoreboard.requiredRR);
            $('.tagetscore-container, .requiredRunRate-container').removeClass('d-none');
            $('.projectedScore-container').addClass('d-none');
        } else {
            // No target: show projected score instead
            $('.tagetscore-container, .requiredRunRate-container').addClass('d-none');
            $elements.projectedScore.text(scoreboard.projected ?? "0");
            $('.projectedScore-container').removeClass('d-none');
        }
    };

    function updateTeamIcon() {
        const teamName = $elements.battingTeamName.text().trim();
        if (!teamName) return;

        const words = teamName.split(' ').filter(Boolean);
        let initials = '';
        if (words.length === 1) {
            initials = words[0].substring(0, 2);
        } else {
            initials = words[0][0] + words[1][0];
        }

        const $icon = $('.team-icon');
        $icon.text(initials.toUpperCase());
    }

    const sendBowlerData = (matchId, teamId, bowlerId) => {
        const chooseBowlerRoute = `/admin/cricket-matches/scoreboard/${matchId}/add-bowler`;

        $.ajax({
            url: chooseBowlerRoute,
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
                    loadFullMatchState();
                    renderBowlingTable(res.bowling);
                    showToast("Bowler Selected..");
                } else {
                    alert(res.message || "Something went wrong!");
                }
            },
            error: function (xhr) {
                console.error("Error saving bowler:", xhr.responseText);
                alert("Error saving bowler.");
            }
        });
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
                url: "/admin/cricket-matches/toss/store",
                method: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
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
                        $('input[name="is_toss_completed"]').val(true);
                        $('#battingTeamName').text(response.batting_team_name);
                        toggleTossInputs("completed");
                        document.getElementById('match-scoreboard').style.display = 'block';
                        // saveMatchState();
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
    // 🔹 Strike Switching
    // ------------------------
    const switchStrike = () => {
        fetchJSON(`/admin/cricket-matches/scoreboard/switch-strike`, {
            method: 'POST',
            body: JSON.stringify({
                match_id: matchId
            })
        }).then(data => {
            if (!data.success) throw new Error(data.message);
            setMatchState({
                striker: data.data.striker,
                nonStriker: data.data.nonStriker
            });
            updatePlayerCard(data.data.striker, 'striker');
            updatePlayerCard(data.data.nonStriker, 'nonStriker');
        }).catch(err => console.error('Error switching strike:', err));
    };

    $(document).on('click', '#switchStrikeBtn', switchStrike);

    // ------------------------
    // 🔹 Add Delivery
    // ------------------------
    const addDelivery = ({
        runs = 0,
        extra = null,
        wicket = null,
        batsmanOut = null,
        legalBall = true
    }) => {
        const state = getMatchState();
        const bowlerId = state.currentBowler;
        if (!bowlerId) return showToast('Select a bowler first', 'error');

        const payload = {
            match_id: matchId,
            striker_id: state.striker?.id ?? null,
            non_striker_id: state.nonStriker?.id ?? null,
            bowler_id: Number(bowlerId),
            runs: Number(runs ?? 0),
            extras: extra ? extra : [],
            wicket: wicket || null,
            batsman_out: batsmanOut || null,
            legal_ball: legalBall
        };

        console.log(payload)
        sendDeliveryToServer(payload);
    };

    const sendDeliveryToServer = (payload) => {
        fetchJSON(`/admin/cricket-matches/scoreboard/store-delivery`, {
            method: 'POST',
            body: JSON.stringify(payload)
        }).then(data => {
            if (!data.success) throw new Error(data.message);
            setMatchState({
                striker: data.updated_state.striker,
                nonStriker: data.updated_state.nonStriker
            });
            showToast(data.message);
            loadFullMatchState(matchId);
            loadCurrentOver();
        }).catch(err => console.error('Error recording delivery:', err));
    };

    // ------------------------
    // 🔹 Load Full Match State
    // ------------------------
    const loadFullMatchState = (matchId) => {
        const tossCompleted = $('input[name="is_toss_completed"]').val() === 'true';
        if (!tossCompleted) return;

        fetchJSON(
            `/admin/cricket-matches/scoreboard/full-match-state/:match_id`.replace(':match_id', matchId))
            .then(data => {
                if (!data.success) return console.error('Failed to load match state:', data.message);
                if (data.match_result) {
                    document.getElementById('match-scoreboard').style.display = 'none';
                    document.getElementById('match-result').style.display = 'block';

                    let winnerWrap = document.getElementById('winner-wrap');
                    if (!winnerWrap) {
                        winnerWrap = document.createElement('div');
                        winnerWrap.id = 'winner-wrap';
                        winnerWrap.className = 'winner-wrap';
                        document.getElementById('match-result').appendChild(winnerWrap);
                    }

                    winnerWrap.innerHTML = `
                                <div class="border"></div>
                                <div class="medal-box"><i class="fas fa-medal"></i></div>
                                <h1>${data.match_result.winning_team}</h1>
                                <h2>${data.match_result.summary}</h2>
                                <div class="winner-ribbon">WINNER</div>
                                <div class="right-ribbon"></div>
                                <div class="left-ribbon"></div>
                            `;

                    winnerWrap.onclick = () => winnerWrap.style.display = 'none';
                } else {
                    document.getElementById('match-scoreboard').style.display = 'block';
                    document.getElementById('match-result').style.display = 'none';

                    setMatchState(data.match_state);
                    $elements.battingTeamName.text(`${data.match_state.team.name}`);
                    updateTeamIcon();

                    updatePlayerCard(data.match_state.striker, 'striker');
                    updatePlayerCard(data.match_state.nonStriker, 'nonStriker');

                    const currentInningsNo = data.match_state.currentInnings;

                    // Find the innings object that matches this number
                    const currentInnings = data.innings.find(inn => inn.innings === currentInningsNo);

                    if (currentInnings) {
                        updateScoreboard(currentInnings.scoreboard);
                        renderBattingTable(currentInnings.batting);
                        renderBowlingTable(currentInnings.bowling);
                        renderPartnerships(currentInnings.partnerships);
                        renderFallOfWickets(currentInnings.fall_of_wickets);
                    } else {
                        console.warn('Current innings data not found');
                    }

                    $('.btn-batsman-out.striker-btn').attr(
                        'data-batsman',
                        data.match_state.striker?.id ?? "striker"
                    );

                    $('.btn-batsman-out.nonstriker-btn').attr(
                        'data-batsman',
                        data.match_state.nonStriker?.id ?? "nonStriker"
                    );
                }
            })
            .catch(err => console.error('Error fetching full match state:', err));
    };

    function loadCurrentOver() {
        const tossCompleted = $('input[name="is_toss_completed"]').val() === 'true';
        if (!tossCompleted) return;

        let chooseBowlerRoute = "/admin/cricket-matches/scoreboard/__MATCH__/current-over";
        const url = chooseBowlerRoute.replace('__MATCH__', matchId);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return console.error(data.message);

                const container = document.getElementById('currentOverDetails');
                container.innerHTML = '';

                data.balls.forEach(ball => {
                    const span = document.createElement('span');

                    if (ball.class) {
                        ball.class.split(' ').forEach(cls => {
                            if (cls.trim()) span.classList.add(cls.trim());
                        });
                    }

                    const ballLabel = String(ball.ball);

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
    // 🔹 Run buttons
    // ------------------------
    $(document).on('click', '.btn-run', function () {
        const run = parseInt($(this).data('run'));
        addDelivery({
            runs: run
        });
    });

    // ------------------------
    // 🔹 Extras buttons
    // ------------------------
    $(document).on('click', '.btn-extra', function () {
        const extraModal = document.getElementById('extraModal');
        if (!extraModal) return console.error("#extraModal not found");

        const modalTitle = extraModal.querySelector('.modal-title');
        const nbSection = document.getElementById('nbSection');
        const wdSection = document.getElementById('wdSection');
        const lbSection = document.getElementById('lbSection');
        const nbRunOutCheckbox = document.getElementById('nbRunOut');
        const nbBatsmanOut = document.getElementById('nbBatsmanOut');
        const type = $(this).data('extra');

        // Reset
        nbSection.classList.add('d-none');
        wdSection.classList.add('d-none');
        lbSection.classList.add('d-none');
        modalTitle.textContent = "Extra";

        if (type === "NB") {
            modalTitle.textContent = "No Ball";
            nbSection.classList.remove('d-none');

            nbRunOutCheckbox.addEventListener('change', function () {
                nbBatsmanOut.classList.toggle('d-none', !this.checked);
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

    document.getElementById("extraForm").addEventListener("submit", function (e) {
        e.preventDefault(); // Prevent form submission

        // Determine which type of extra is selected
        let extra = null;
        let batsmanOut = null;
        let legalBall = true;

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
            legalBall = false;

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
            legalBall = false;
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
            legalBall = true; // LB is legal
        }

        // Now call addDelivery with extra
        addDelivery({
            runs: 0,
            extra: extra,
            wicket: extra?.run_out ? "run_out" : null,
            batsmanOut: batsmanOut,
            legalBall: legalBall
        });

        // Close modal after submission
        const extraModal = bootstrap.Modal.getInstance(document.getElementById("extraModal"));
        extraModal.hide();
    });

    // ------------------------
    // 🔹 Wicket buttons
    // ------------------------
    $(document).on('click', '.btn-wicket', function () {
        const wicketType = $(this).data('wicket');
        currentWicket = wicketType;
        const modal = new bootstrap.Modal(document.getElementById('wicketModal'));
        modal.show();
    });

    $(document).on('click', '.btn-wicket-type', function () {
        const type = $(this).data('wicket');
        $(".wicket-extra").addClass("d-none");

        if (type === "Run Out") {
            document.getElementById('runOutOptions').classList.toggle('d-none', type !== 'Run Out');
        }
        else if (type === "Caught") {
            $("#caughtOptions").removeClass("d-none");
            loadBowlingTeamPlayers('caughtBySelect');
        }
        else if (type === "Stumped") {
            $("#stumpedOptions").removeClass("d-none");
            loadBowlingTeamPlayers('stumpedBySelect');
        }
        else {
            finalizeWicket({
                type,
                batsmanOut: $('.btn-batsman-out.striker-btn').data('batsman')
            });
        }
    });

    $(document).on('click', '.btn-batsman-out', function () {
        finalizeWicket({
            type: 'Run Out',
            batsmanOut: $(this).data('batsman')
        });
    });

    let stumpedData = {
        keeperId: null,
        batsmanOut: null
    };

    $(document).on('change', '#stumpedBySelect', function () {
        stumpedData.keeperId = $(this).val();
        stumpedData.batsmanOut = getMatchState().striker.id;

        tryFinalizeStumped();
    });

    $(document).on('change', '#caughtBySelect', function () {
        const matchState = getMatchState();
        const selectedFielder = $(this).val();
        const striker = matchState.striker.id;
        finalizeWicket({
            type: 'caught',
            batsmanOut: striker,
            fielderId: selectedFielder
        });
    });

    // ------------------------
    // 🔹 Wicket functions
    // ------------------------
    function tryFinalizeStumped() {
        if (stumpedData.keeperId && stumpedData.batsmanOut) {
            finalizeWicket({
                type: 'stumped',
                batsmanOut: stumpedData.batsmanOut,
                fielderId: stumpedData.keeperId
            });

            stumpedData.keeperId = null;
            stumpedData.batsmanOut = null;

            $('#stumpedOptions').addClass('d-none');
            $('#stumpedBySelect').val('');
        }
    }

    function finalizeWicket({ type, batsmanOut, fielderId = null }) {
        const modalEl = document.getElementById('wicketModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();

        setTimeout(() => {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        }, 100);

        // Build extras object
        const extras = {};
        if (type === 'Run Out') {
            extras.run_out = true;
        } else if (type === 'caught') {
            extras.run_out = false;
            extras.caught_by = fielderId;
        } else if (type === 'stumped') {
            extras.run_out = false;
            extras.stumped_by = fielderId;
        }

        const finalBatsmanOut = batsmanOut || null;

        addDelivery({
            runs: 0,
            extras: Object.keys(extras).length ? extras : null,
            wicket: type,
            batsman_out: finalBatsmanOut,
            legalBall: true
        });
    }


    // ------------------------
    // 🔹 Load bowling team players
    // ------------------------
    function loadBowlingTeamPlayers(selectId) {
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="">Select Fielder</option>';
        const matchState = getMatchState();
        const bowlingTeamPlayers = matchState.bowlingTeamPlayers;

        if (!bowlingTeamPlayers.length) {
            select.innerHTML = `<option value="">No players available</option>`;
            return;
        }

        bowlingTeamPlayers.forEach(stat => {
            const opt = document.createElement('option');
            if (stat.player.id != matchState.currentBowler) {
                opt.value = stat.id;
                opt.textContent = stat.player.user.full_name;
                select.appendChild(opt);
            }
        });
    }

    // ------------------------
    // 🔹 Fetch & Initialize Bowling Team Players
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
            },
            error: function (xhr) {
                console.error('Failed to fetch bowling team players:', xhr.responseText);
            }
        });
    };

    // ------------------------
    // When a bowler is selected from Select2
    // ------------------------
    $('#bowler-select').on('select2:select', function (e) {
        const bowlerId = e.params.data.id;
        const teamId = $('#bowling_team_id').val();

        const chooseBowlerRoute = `/admin/cricket-matches/scoreboard/${matchId}/add-bowler`;

        $.ajax({
            url: chooseBowlerRoute,
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
                    renderBowlingTable(res.bowling); // Use your table rendering helper
                    showToast("Bowler Selected..");
                } else {
                    alert(res.message || "Something went wrong!");
                }
            },
            error: function (xhr) {
                console.error("Error saving bowler:", xhr.responseText);
                alert("Error saving bowler.");
            }
        });
    });

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

    function loadYetToBatPlayers() {
        fetch('/api/matches/yet-to-bat/' + matchId)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                matchState.battingTeamId = data.battingTeamId;

                const list = document.getElementById('yetToBatList');
                list.innerHTML = '';

                if (!data.players || !data.players.length) {
                    list.innerHTML = '<li class="list-group-item text-muted">No batting order available</li>';
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

    // ----------------------------------
    // 🔹 Select Players From Yet To Bat
    // ----------------------------------
    window.selectBatsman = function (playerId) {
        const matchState = JSON.parse(localStorage.getItem(stateKey) || "{}");
        const battingTeamId = matchState.battingTeamId;

        let role = null;

        if (!matchState.striker) {
            role = 'on-strike';
        } else if (!matchState.nonStriker) {
            role = 'batting';
        } else {
            Swal.fire('Both striker and non-striker are already selected.');
            return;
        }

        fetch("/admin/cricket-matches/scoreboard/select-batsman", {
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

    document.getElementById('yetToBatList').addEventListener('click', function (e) {
        if (!e.target.classList.contains('select-player-btn')) return;
        const card = e.target.closest('.player-card');
        const playerId = card.dataset.playerId;
        selectBatsman(playerId);
    });

    const toggleTossInputs = (status) => {
        const $tossInputs = $('input[name="toss-team"], input[name="toss-decision"]');
        if (status === 'completed') {
            $tossInputs.prop('disabled', true);
            $('.match-toss-container').remove();
        } else {
            $tossInputs.prop('disabled', false);
            console.log(status)
        }
    };

    $("#start-match").select2({
        placeholder: "Select Status",
        width: "25%",
        minimumResultsForSearch: -1
    });

    $('#start-match').on('change', function () {
        var selectedStatus = $(this).val();
        toggleTossInputs(selectedStatus);
        $.ajax({
            url: '/admin/cricket-matches/start/' + matchId,
            method: 'GET',
            data: { status: selectedStatus },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Status Updated!',
                        text: `Match status has been changed to "${selectedStatus}".`,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Failed to update match status.', 'error');
                }
            },
            error: function (err) {
                Swal.fire('Error', 'Failed to update match status.', 'error');
            }
        });
    });

    // ------------------------
    // 🔹 Initialization
    // ------------------------
    loadFullMatchState(matchId);
    fetchBowlingTeamPlayers();
    toggleTossInputs($('#start-match').val());
    loadYetToBatPlayers();
    loadCurrentOver();
});