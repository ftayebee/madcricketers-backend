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
        strikerStrikeRate: $('#strikerStrikeRate'),
        strikerBalls: $('#strikerBallsFaced'),
        nonStrikerName: $('#nonStrikerName'),
        nonStrikerRuns: $('#nonStrikerRuns'),
        nonStrikerStrikeRate: $('#nonStrikerStrikeRate'),
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

    const tossCompleted = $('input[name="is_toss_completed"]').val() === 'true';

    let stumpedData = {
        keeperId: null,
        batsmanOut: null
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

    const storeLocalState = (key, value) => {
        localStorage.setItem(key, JSON.stringify(value));
    };

    const loadLocalState = (key) => {
        const value = localStorage.getItem(key);
        return value ? JSON.parse(value) : null;
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
    if (document.querySelector('.match-scoreboard') && !tossCompleted) {
        document.querySelectorAll('.match-scoreboard').forEach(element => {
            element.style.display = 'none';
        });
        $('#match-result').addClass('d-none');
    }

    const updatePlayerCard = (player, type) => {
        if (!player) {
            $elements[`${type}Name`].text('Choose Player');
            $elements[`${type}Runs`].text('00');
            $elements[`${type}Balls`].text('0');
            $elements[`${type}StrikeRate`].text('00.00');
            $elements[`${type}Actions`].html('');
            return;
        }

        $elements[`${type}Name`].text(player.name);
        $elements[`${type}Runs`].text(player.runs ?? 0);
        $elements[`${type}Balls`].text(player.balls ?? 0);
        $elements[`${type}StrikeRate`].text(
            player.balls > 0
                ? ((Number(player.runs || 0) / Number(player.balls)) * 100).toFixed(2)
                : '0.00'
        );
    };

    const updateScoreboard = (scoreboard) => {
        $elements.currentScore.text(`${scoreboard.runs} / ${scoreboard.wickets}`);
        $elements.currentOvers.text(`${scoreboard.overs} / ${scoreboard.totalOvers}`);
        $elements.currentCRR.text(`${scoreboard.currentCRR}`);

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

    const renderCurrentBowlerCard = (bowler) => {
        if (bowler) {
            $('#currentBowlerDetails').html(`
                <strong style="display: block;">${bowler.name}</strong>
                <div class="d-flex flex-wrap gap-2 mt-1 dt-current-bowler-info" data-bowler-id="${bowler.id}">
                    <span>Ov: ${bowler.overs}</span>
                    <span>R: ${bowler.runs_conceded}</span>
                    <span>W: ${bowler.wickets}</span>
                </div>
            `);
        } else {
            $('#currentBowlerDetails').html('No bowler selected');
        }
    };

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
                    renderCurrentBowlerCard(res.bowling);
                    showToast("Bowler Selected..");
                    window.location.reload();
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

    function getPlayersList(teamId, type) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/admin/cricket-matches/players-list',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    match_id: matchId,
                    team_id: teamId,
                    type: type
                },
                success: function (res) {
                    if (res.success && Array.isArray(res.data)) {
                        resolve(res.data);
                    } else {
                        reject('Invalid players list response');
                    }
                },
                error: function (err) {
                    reject(err);
                }
            });
        });
    }

    // Global variables
    let isSelectingBowler = false;
    let currentOverNumber = 0;
    let isFirstOverOfInnings = false;

    function saveOverState() {
        localStorage.setItem('isSelectingBowler', isSelectingBowler ? 'true' : 'false');
        localStorage.setItem('currentOverNumber', currentOverNumber.toString());
        localStorage.setItem('isFirstOverOfInnings', isFirstOverOfInnings ? 'true' : 'false');
    }

    function clearOverState() {
        localStorage.removeItem('isSelectingBowler');
        localStorage.removeItem('currentOverNumber');
        localStorage.removeItem('isFirstOverOfInnings');
    }

    function checkCurrentOver() {
        fetch(`/admin/cricket-matches/scoreboard/${matchId}/current-over`)
            .then(response => response.json())
            .then(data => {
                const legalBalls = parseInt(data.legalBalls) || 0;
                const overNum = parseInt(data.overNumber) || 0;

                // Check if this is the first over of the innings
                if (overNum === 1 && legalBalls === 0 && !isFirstOverOfInnings) {
                    isFirstOverOfInnings = true;
                    saveOverState();
                    openBowlerModal();
                    return;
                }

                // Check if over has ended (6 legal balls)
                if (legalBalls >= 6 && !isSelectingBowler) {
                    isSelectingBowler = true;
                    currentOverNumber = overNum + 1; // Next over number
                    saveOverState();
                    openBowlerModal();
                }

                // Update current over number
                if (overNum !== currentOverNumber) {
                    currentOverNumber = overNum;
                    saveOverState();
                }
            })
            .catch(error => console.error('Error fetching current over:', error));
    }

    // Call when bowler is selected
    function onBowlerSelected() {
        isSelectingBowler = false;
        isFirstOverOfInnings = false; // Reset after first over
        saveOverState();
    }

    // Start periodic over checking
    function startCheckingOver() {
        setInterval(() => {
            checkCurrentOver();
        }, 2000);
    }

    // Initialize on page load
    function initializeOverSystem() {
        // Load saved state
        const savedIsSelectingBowler = localStorage.getItem('isSelectingBowler');
        const savedCurrentOver = parseInt(localStorage.getItem('currentOverNumber')) || 0;
        const savedIsFirstOver = localStorage.getItem('isFirstOverOfInnings');

        // Set initial states
        isSelectingBowler = savedIsSelectingBowler === 'true';
        currentOverNumber = savedCurrentOver;
        isFirstOverOfInnings = savedIsFirstOver === 'true';

        // Check if we need to show modal on page load
        if (isSelectingBowler) {
            // Only show modal if we're actually in an over selection state
            checkCurrentOver(); // This will trigger modal if conditions are met
        } else if (isFirstOverOfInnings) {
            // Show modal for first over of innings
            openBowlerModal();
        }

        startCheckingOver();
    }

    // Modified openBowlerModal to prevent duplicate openings
    async function openBowlerModal() {
        // Prevent opening if already selecting or modal already shown
        if (isSelectingBowler && $('#bowlerModal').hasClass('show')) {
            console.log("Modal already open, skipping...");
            return;
        }

        try {
            loadFullMatchState(matchId);
            const matchState = getMatchState();
            const bowlers = await getPlayersList(matchState.bowlingTeamId, 'bowling');
            console.log(bowlers);
            populateBowlerModal(bowlers);

            $('#bowlerModal').modal('show');
            isSelectingBowler = true;
            saveOverState();

        } catch (err) {
            console.error(err);
            $('#bowlerList').html(`
                <div class="alert alert-danger mb-0">
                    Failed to load bowlers
                </div>
            `);
        }
    }

    function populateBowlerModal(bowlingPlayers) {
        const bowlerList = $('#bowlerList');
        bowlerList.empty();

        if (!bowlingPlayers.length) {
            bowlerList.html(`
                <div class="alert alert-warning mb-0">
                    No bowlers available
                </div>
            `);
            return;
        }
        currentOverNumber = (parseFloat($('#currentOvers').text().split(' / ')[0]) || 1) + 1;
        // Add header showing which over this is for
        const modalTitle = isFirstOverOfInnings
            ? "Select Bowler for First Over of Innings"
            : `Select Bowler for Over ${currentOverNumber}`;

        $('.modal-title').text(modalTitle);

        bowlingPlayers.forEach(player => {
            const img = player.image_url ?? '/images/player-placeholder.png';
            const overs = player.overs_bowled ?? '0.0';
            const style = player.bowling_style ?? '—';

            const card = `
                <div class="card bowler-card mb-2"
                    role="button"
                    data-bowler-id="${player.id}"
                    data-bowler-name="${player.name}">

                    <div class="card-body p-2 d-flex align-items-center gap-3">

                        <img src="${img}"
                            class="rounded-circle"
                            style="width:50px;height:50px;object-fit:cover;"
                            alt="${player.name}">

                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">${player.name}</h6>

                            <small class="text-muted d-block">
                                Overs: <strong>${overs}</strong>
                            </small>

                            <small class="text-muted d-block">
                                Style: ${style}
                            </small>
                        </div>

                    </div>
                </div>
            `;

            bowlerList.append(card);
        });

        bowlerList.off('click', '.bowler-card');
        bowlerList.on('click', '.bowler-card', function () {
            const bowlerId = $(this).data('bowler-id');
            sendBowlerData(matchId, matchState.bowlingTeamId, bowlerId);

            $('#bowlerModal').modal('hide');
            onBowlerSelected();
        });
    }

    function resetForNewInnings() {
        clearOverState();
        isSelectingBowler = false;
        currentOverNumber = 0;
        isFirstOverOfInnings = false;
    }

    // Initialize when page loads
    initializeOverSystem();

    // ------------------------
    // 🔹 Toss Selection
    // ------------------------
    $('#btn-save-toss').on('click', function () {
        let selectedTeam = $('input[name="toss-team"]:checked').val();
        let selectedDecision = $('input[name="toss-decision"]:checked').val();
        submitTossIfReady(selectedTeam, selectedDecision);
    });

    function submitTossIfReady(selectedTeam, selectedDecision) {
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
                        window.location.reload()
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

    function finalizeWicket({ type, batsmanOut, fielderId = null, runs = 0 }) {
        const modalEl = document.getElementById('wicketModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();

        setTimeout(() => {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        }, 100);

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
        extras.type = type;
        extras.runs = runs;

        const finalBatsmanOut = batsmanOut || null;

        if (typeof finalBatsmanOut !== 'number') {
            console.log("Final Batsman Out ID: " + finalBatsmanOut);
            showToast('Please select the batsman who got out or you need to select striker first.', 'error');

            return;
        }

        addDelivery({
            runs: 0,
            extra: Object.keys(extras).length ? extras : null,
            wicket: type,
            batsman_out: finalBatsmanOut,
            legalBall: true
        });
    }

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
            type: 'run_out',
            batsmanOut: $(this).data('batsman')
        });
    });

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
            fielderId: selectedFielder,
            runs: 0
        });
    });

    function resetWicketForm() {
        $('#runOutOptions').addClass('d-none');
        $('#caughtOptions').addClass('d-none');
        $('#stumpedOptions').addClass('d-none');
        $('#caughtBySelect').val('');
        $('#stumpedBySelect').val('');
    }

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
        const bowlerId = $('.dt-current-bowler-info').data('bowler-id');
        if (!bowlerId) return showToast('Select a bowler first', 'error');
        if (!state.striker && !state.nonStriker) {
            showToast('Please Select Striker And Non-Striker Batsman', 'error');
            return;
        }

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

        sendDeliveryToServer(payload);
    };

    const sendDeliveryToServer = (payload) => {
        fetchJSON(`/admin/cricket-matches/scoreboard/store-delivery`, {
            method: 'POST',
            body: JSON.stringify(payload)
        }).then(data => {
            console.log("Delivery Response Data: ", data);
            if (!data.success) throw new Error(data.message);
            setMatchState({
                striker: data.updated_state.striker,
                nonStriker: data.updated_state.nonStriker
            });
            showToast(data.message);
            loadFullMatchState(matchId);
            loadCurrentOver();

            if (data.isOverEnded && !data.isInningsEnded) {
                isSelectingBowler = true;
                saveOverState();
                openBowlerModal();
            }

            const inningsEnded = (data.isInningsEnded == true || data.isInningsEnded === "true" || data.isInningsEnded == 1);
            if (inningsEnded) {
                loadYetToBatPlayers();
            }

            if (payload.wicket != null) {
                window.location.reload();
            }
        }).catch(err => {
            console.error('Error recording delivery:', err);

            showToast(err.message, 'error', 4000);
        });
    };

    const undoLastDelivery = async () => {
        try {
            const undoBtn = document.querySelector('.btn-undo-ball');
            if (undoBtn) undoBtn.disabled = true;

            const response = await fetch(`/admin/cricket-matches/scoreboard/undo-last-delivery`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({ match_id: matchId })
            });

            const data = await response.json();

            if (!data.success) throw new Error(data.message);

            showToast(data.message);

            await loadFullMatchState(matchId);
            await loadCurrentOver(matchId);
        } catch (err) {
            console.error('Error undoing last delivery:', err);
            showToast('Failed to undo last delivery. Please try again.');
        } finally {
            const undoBtn = document.querySelector('.btn-undo-ball');
            if (undoBtn) undoBtn.disabled = false;
        }
    };

    // ------------------------
    // 🔹 Load Full Match State
    // ------------------------
    const loadFullMatchState = (matchId) => {
        if (!tossCompleted) return;
        matchId = matchId || $('input[name="toss_match_id"]').val();
        fetchJSON(
            `/admin/cricket-matches/scoreboard/full-match-state/:match_id`.replace(':match_id', matchId))
            .then(data => {
                if (!data.success) return console.error('Failed to load match state:', data.message);
                if (data.match_result) {
                    console.log("Match Result Data: ", data.match_result);
                    document.querySelectorAll('.match-scoreboard').forEach(element => {
                        element.style.display = 'none';
                    });
                    $('#match-result').removeClass('d-none');

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


                    $('#team-a-name').text(data.match_result.scoreboards.teamA.team.name);
                    $('#team-a-runs').text(data.match_result.scoreboards.teamA.runs + ' / ' + data.match_result.scoreboards.teamA.wickets);
                    $('#team-a-overs').text("From " + data.match_result.scoreboards.teamA.overs + ' Overs');
                    $('#team-a-innings').text(data.match_result.scoreboards.teamA.innings + (data.match_result.scoreboards.teamA.innings == 1 ? 'st Innings' : 'nd Innings'));
                    $('#team-b-name').text(data.match_result.scoreboards.teamB.team.name);
                    $('#team-b-runs').text(data.match_result.scoreboards.teamB.runs + ' / ' + data.match_result.scoreboards.teamB.wickets);
                    $('#team-b-overs').text("From " + data.match_result.scoreboards.teamB.overs + ' Overs');
                    $('#team-b-innings').text(data.match_result.scoreboards.teamB.innings + (data.match_result.scoreboards.teamB.innings == 1 ? 'st Innings' : 'nd Innings'));
                } else {
                    document.querySelectorAll('.match-scoreboard').forEach(element => {
                        element.style.display = 'block';
                    });
                    $('#match-result').addClass('d-none');

                    setMatchState(data.match_state);
                    $elements.battingTeamName.text(`${data.match_state.team.name}`);

                    updatePlayerCard(data.match_state.striker, 'striker');
                    updatePlayerCard(data.match_state.nonStriker, 'nonStriker');

                    const currentInningsNo = data.match_state.currentInnings;

                    // Find the innings object that matches this number
                    const currentInnings = data.innings.find(inn => inn.innings === currentInningsNo);

                    if (currentInnings) {
                        updateScoreboard(currentInnings.scoreboard);
                        renderCurrentBowlerCard(data.match_state.currentBowler);
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

    function formatBallType(type) {
        return type
            .replace('-', ' ')
            .replace(/\b\w/g, c => c.toUpperCase());
    }

    function loadCurrentOver() {
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
                    const wrapper = document.createElement('div');
                    wrapper.classList.add('over-ball-wrapper');

                    const span = document.createElement('span');
                    span.classList.add('over-ball');

                    if (ball.class) {
                        ball.class.split(' ').forEach(cls => {
                            if (cls.trim()) span.classList.add(cls.trim());
                        });
                    }

                    span.innerText = ball.ball;

                    if (String(ball.ball).includes('W')) {
                        span.classList.add('wicket-ball');
                    }

                    wrapper.appendChild(span);

                    if (ball.type && ball.type !== 'normal') {
                        const label = document.createElement('small');
                        label.classList.add('ball-type-label');

                        label.innerText = ball.label || formatBallType(ball.type);

                        wrapper.appendChild(label);
                    }

                    container.appendChild(wrapper);
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
        const bySection = document.getElementById('bySection');
        const nbRunOutCheckbox = document.getElementById('nbRunOut');
        const nbBatsmanOut = document.getElementById('nbBatsmanOut');
        const type = $(this).data('extra');

        // Reset everything
        nbSection.classList.add('d-none');
        wdSection.classList.add('d-none');
        lbSection.classList.add('d-none');
        bySection.classList.add('d-none');
        modalTitle.textContent = "Extra";

        // Reset radio buttons
        document.querySelectorAll('#extraForm input[type="radio"]').forEach(radio => {
            radio.checked = false;
        });

        // Reset checkboxes
        if (nbRunOutCheckbox) nbRunOutCheckbox.checked = false;

        // Reset player cards
        document.querySelectorAll('.player-card').forEach(card => {
            card.classList.remove('selected');
        });
        document.querySelectorAll('.player-check').forEach(check => {
            check.classList.add('d-none');
        });

        // Reset player error
        const playerError = document.getElementById('playerError');
        if (playerError) playerError.textContent = '';

        if (type === "NB") {
            modalTitle.textContent = "No Ball";
            nbSection.classList.remove('d-none');

            const newCheckbox = nbRunOutCheckbox.cloneNode(true);
            nbRunOutCheckbox.parentNode.replaceChild(newCheckbox, nbRunOutCheckbox);
            $('#mdl-strikerName').text(matchState.striker ? matchState.striker.name : 'Choose Player');
            $('#mdl-nonStrikerName').text(matchState.nonStriker ? matchState.nonStriker.name : 'Choose Player');
            console.log("Resetting player selection");

            newCheckbox.addEventListener('change', function () {
                nbBatsmanOut.classList.toggle('d-none', !this.checked);

                if (!this.checked) {
                    document.querySelectorAll('.player-radio').forEach(radio => {
                        radio.checked = false;
                    });
                    document.querySelectorAll('.player-card').forEach(card => {
                        card.classList.remove('selected');
                    });
                    document.querySelectorAll('.player-check').forEach(check => {
                        check.classList.add('d-none');
                    });
                }
            });

            // Add player selection functionality
            document.querySelectorAll('.player-card-label').forEach(label => {
                const radio = label.querySelector('input[type="radio"]');
                const card = label.querySelector('.player-card');
                const check = label.querySelector('.player-check');

                label.addEventListener('click', function (e) {
                    // Only select if run out is checked
                    if (!nbRunOutCheckbox.checked) {
                        e.preventDefault();
                        return;
                    }

                    // Deselect all
                    document.querySelectorAll('.player-card').forEach(c => {
                        c.classList.remove('selected');
                    });
                    document.querySelectorAll('.player-check').forEach(c => {
                        c.classList.add('d-none');
                    });

                    // Select this one
                    radio.checked = true;
                    card.classList.add('selected');
                    check.classList.remove('d-none');

                    // Clear error
                    const playerError = document.getElementById('playerError');
                    if (playerError) playerError.textContent = '';
                });
            });

            // Set default radio button for runs
            const defaultNbRun = document.getElementById('nbRun0');
            if (defaultNbRun) defaultNbRun.checked = true;

        } else if (type === "WD") {
            modalTitle.textContent = "Wide Ball";
            wdSection.classList.remove('d-none');

            // Set default radio button for runs (1 run)
            const defaultWdRun = document.getElementById('wdRun1');
            if (defaultWdRun) defaultWdRun.checked = true;

        } else if (type === "LB") {
            modalTitle.textContent = "Leg Bye";
            lbSection.classList.remove('d-none');

            // Set default radio button for runs
            const defaultLbRun = document.getElementById('lbRun0');
            if (defaultLbRun) defaultLbRun.checked = true;
        } else if (type === "BY") {
            modalTitle.textContent = "Extra: Bye";
            bySection.classList.remove('d-none');
        }
    });

    document.getElementById("extraForm").addEventListener("submit", function (e) {
        e.preventDefault();

        let extra = null;
        let batsmanOut = null;
        let legalBall = true;
        let valid = true;

        // No Ball
        const nbSection = document.getElementById("nbSection");
        if (!nbSection.classList.contains("d-none")) {
            const nbRun = document.querySelector('input[name="nbRuns"]:checked');
            const runOutChecked = document.getElementById("nbRunOut").checked;

            if (!nbRun) {
                alert("Please select runs for No Ball");
                valid = false;
                return;
            }

            extra = {
                type: "NB",
                runs: nbRun ? Number(nbRun.value) : 0,
                run_out: runOutChecked
            };
            legalBall = false;

            if (runOutChecked) {
                const selectedBatsman = document.querySelector('input[name="player_id"]:checked');
                if (selectedBatsman) {
                    batsmanOut = selectedBatsman.value;
                } else {
                    const playerError = document.getElementById('playerError');
                    if (playerError) playerError.textContent = 'Please select a batsman for run out';
                    valid = false;
                    return;
                }
            }
        }

        // Wide Ball
        const wdSection = document.getElementById("wdSection");
        if (!wdSection.classList.contains("d-none")) {
            const wdRun = document.querySelector('input[name="wdExtraRuns"]:checked');

            if (!wdRun) {
                alert("Please select extra runs for Wide Ball");
                valid = false;
                return;
            }

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

            if (!lbRun) {
                alert("Please select runs for Leg Bye");
                valid = false;
                return;
            }

            extra = {
                type: "LB",
                runs: lbRun ? Number(lbRun.value) : 0,
                run_out: false
            };
            legalBall = true;
        }

        if (!valid) return;

        // Call your addDelivery function
        addDelivery({
            runs: 0,
            extra: extra,
            wicket: extra?.run_out ? "run_out" : null,
            batsmanOut: batsmanOut,
            legalBall: legalBall
        });

        // Close modal
        const extraModal = bootstrap.Modal.getInstance(document.getElementById("extraModal"));
        if (extraModal) extraModal.hide();
    });

    // Reset form when modal is hidden
    document.getElementById('extraModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('extraForm').reset();
        document.querySelectorAll('.player-card').forEach(card => {
            card.classList.remove('selected');
        });
        document.querySelectorAll('.player-check').forEach(check => {
            check.classList.add('d-none');
        });
        const playerError = document.getElementById('playerError');
        if (playerError) playerError.textContent = '';
    });

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

        console.log("Bowling Team Players: ", bowlingTeamPlayers);

        bowlingTeamPlayers.forEach(stat => {
            const opt = document.createElement('option');
            if (stat.player.id != matchState.currentBowler) {
                opt.value = stat.player_id;
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

    // ----------------------------------
    // 🔹 Select Players From Yet To Bat
    // ----------------------------------
    $('#flt-player').on('input', function () {
        const searchTerm = this.value.toLowerCase();

        document.querySelectorAll('#yetToBatList .player-card').forEach(card => {
            const attrEl = card.querySelector('.flt-attribute');
            const playerName = attrEl?.dataset.playerName?.toLowerCase() || '';

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
                                <div class="align-items-center p-2 border flt-attribute"  data-player-name="${player.full_name}"
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
                role: role,
                currentInnings: matchState.currentInnings
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
                    document.getElementById("mdl-strikerName").innerText = matchState.striker.name;
                    document.getElementById("strikerRuns").innerText = "00";
                    document.getElementById("strikerBallsFaced").innerText = "0";
                } else {
                    document.getElementById("nonStrikerName").innerText = matchState.nonStriker.name;
                    document.getElementById("mdl-nonStrikerName").innerText = matchState.nonStriker.name;
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

    if (document.getElementById('yetToBatList')) {
        document.getElementById('yetToBatList').addEventListener('click', function (e) {
            if (!e.target.classList.contains('select-player-btn')) return;
            const card = e.target.closest('.player-card');
            const playerId = card.dataset.playerId;
            selectBatsman(playerId);
        });
    }

    const toggleTossInputs = (status) => {
        const $tossInputs = $('input[name="toss-team"], input[name="toss-decision"]');
        if (status === 'completed') {
            $tossInputs.prop('disabled', true);
            $('.match-toss-container').remove();
        } else {
            $tossInputs.prop('disabled', false);
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

    function sendInningsStatus() {
        $.ajax({
            url: '/admin/cricket-matches/scoreboard/mark-innings-complete/' + matchId,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Status Updated!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    $('.match-scoreboard').hide();
                    $('#match-result').show();
                    console.log("Hidden scoreboard show result");

                    const matchData = response.matchData;
                    $('#match-title').text(matchData.title);
                    $('#match-result_summary').text(matchData.result_summary);
                    loadFullMatchState(matchId);
                    window.location.reload();
                } else {
                    Swal.fire('Error', response.message || 'Failed to update match status.', 'error');
                }
            },
            error: function (err) {
                Swal.fire('Error', 'Failed to update match status.', 'error');
            }
        });
    }

    $('.btn-complete-innings').on('click', function () {
        sendInningsStatus();
    });

    // ------------------------
    // 🔹 Initialization
    // ------------------------
    $('#btn-trigger-bowler-modal').on('click', function () {
        openBowlerModal();
    });

    const lastOver = localStorage.getItem('lastOver');
    if (lastOver) {
        console.log('Resuming from last over:', lastOver);
    }

    if (tossCompleted) {
        loadFullMatchState(matchId);
        fetchBowlingTeamPlayers();
        loadYetToBatPlayers();
        loadCurrentOver();
    }
    toggleTossInputs($('#start-match').val());
    startCheckingOver();
    $('.btn-undo-ball').on('click', function () {
        undoLastDelivery();
    });

    $('#btn-reset-wicket-form').on('click', function () {
        resetWicketForm();
    });

    storeLocalState('isInningsEnded', false);
    storeLocalState('inningsEndAlertShown', false);

    function checkCurrentInningsEnd() {
        let currentScore = $('#currentScore').text();
        let wicketsFallen = currentScore.split('/')[1].trim();
        let score = parseInt(currentScore.split('/')[0].trim());
        const matchState = getMatchState();
        let battingTeamPlayers = matchState.team.total_players || 2;

        if (parseInt(wicketsFallen) == battingTeamPlayers - 1 && !loadLocalState('isInningsEnded')) {
            storeLocalState('isInningsEnded', true);
            $('.btn-delivery-section').hide();
            $('.current-bowler-section').hide();
            $('.yettobat-section').hide();
            $('#bowlerModal').modal('hide');
            $('.btn-undo-ball').prop('disabled', true);
            $('.btn-complete-innings').removeClass('btn-outline-danger');
            $('.btn-complete-innings').addClass('btn-success');
            $('.target-window').removeClass('d-none');
            let targetRuns = parseInt($('#targetInfo').data('target-runs')) || 0;
            $('#targetInfo').text(`Target: ${score + 1} runs`);
            Swal.fire('Innings has ended as all batsmen are out.');
            storeLocalState('inningsEndAlertShown', true);
        }
    }

    setInterval(() => {
        checkCurrentInningsEnd();
    }, 2000);
});