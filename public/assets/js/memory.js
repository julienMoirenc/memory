/**
 *
 */

/*the jQuery way to trigger things when the dom is ready*/
$(document).ready(function () {


    /* 
    Memory current game state :
    Used by the setState function to init / clean out each view
    There are only two view here :
    - menu to show menu
    - game to show game (wouahou!)
    */
    var state = undefined;

    //Memory game object
    var memory = {
        key: null, // key of the game (used on server to verify if solution solves problem and track solving time)
        problem: null, // problem sent by the web server
        solution: null, // solution provided by the player
        timer: null, // timer runing while the player tries to solve the problem
        interval: null, // timer for bar refresh
        step: 0, //step in the game - 0 : no fruit choosen, 1 - first fruit choosen
        fruit1: null, //the first choosen fruit
        fruit2: null, //the second choosen fruit
    };

    /////////////////////////////////////////////
    //state game change functions:
    //these functions are used to init and clean
    //out each state of the game
    //(i.e : mainly to start/stop timers, retrieve data and manage events)
    /////////////////////////////////////////////

    /**
     * init menu state :
     * - retrieve best scores from server and show them
     * - attach click event on the menu button
     */
    function menuInit() {
        function setBestScores() {
            //retreive best scores from server
            axios
                .get("/api/score/best")
                .then(function (response) {

                    //and show it to the user
                    const scores = response.data;

                    const container = $(".best-scores__scores");
                    container.empty();

                    const ul = $("<ul>").appendTo(container);

                    scores.forEach(function (item, index) {
                        ul.append("<li>" + item + "</li>");
                    });
                })
                .catch(function (error) {
                    console.log("unable to retrieve best scores...");
                });
        }

        setBestScores();

        //attach a click event on the menu button
        $(".state-menu button").click(function () {
            setState("game");
        });
    }


    /**
     * clean out menu state :
     * - unbind click event on menu play button
     */
    function menuCleanout() {
        $(".state-menu button").unbind("click");
    }

    /**
     * init game state :
     * - retrieve a problem from the server
     * - init the memory object
     * - init the game board with the problem
     * - lauch game timeout timer
     * - launch time bar refreshess interval
     * - attach click event on each game card
     */
    function gameInit() {

        //retreive new game from server
        axios
            .get("/api/game/new")
            .then(function (response) {

                //init memory object with it
                memory = response.data;
                memory.step = 0;
                memory.solution = [];
                memory.timer = null;
                memory.interval = null;
                memory.start = new Date().getTime();

                //show it
                const container = $(".game-board");
                container.empty();

                const ul = $("<ul>").appendTo(container);

                memory.problem.forEach(function (item, index) {
                    ul.append(
                        '<li class="' + item + '" data-index="' +
                        index +
                        '" data-fruit="' +
                        item +
                        '"></li>'
                    );
                });

                //function to display messages to user
                function showMessage(message) {
                    $(".message__message")
                        .empty()
                        .append("<p>" + message + "</p>");

                    $(".message").show();
                }

                //hide message by default
                $(".message").hide();

                //event handler for return to menu button
                $('.message__button-menu').click(function () {
                    setState('menu');
                });

                //event handler for shootagain button
                $('.message__button-shootagain').click(function () {
                    setState('game');
                });

                //end of game timer launch
                memory.timer = setTimeout(function () {
                    memory.timer = null;
                    clearInterval(memory.interval);
                    showMessage("Dommage !");
                }, memory.timeout);

                //progress bar refresh interval launch
                memory.interval = setInterval(function () {
                    percent =
                        ((new Date().getTime() - memory.start) / memory.timeout) * 100;
                    $(".timer__scrollbar").css("width", percent + "%");
                }, 50);

                //card game click handler
                container.find("li").click(function () {
                    const target = $(this);
                    const isHidden = !target.hasClass("show");
                    const gameFinished = memory.timer === null;

                    //a click can be done only if the game is not finished and the card is not shon...
                    if (isHidden && !gameFinished) {

                        if (memory.step == 0) {
                            /*first step : 
                            - hide old cards (found card will still be shown),
                            - show clicked card,
                            - memorized clicked card
                            */
                            if (memory.fruit1) memory.fruit1.removeClass("show");
                            if (memory.fruit2) memory.fruit2.removeClass("show");
                            target.addClass("show");
                            memory.fruit1 = target;
                        } else if (memory.step == 1) {
                            /*second step : 
                            - show clicked card,
                            - memorized clicked card
                            - verify the cards match and store in in the solution array
                            - if solution is complete, show success message and send solution to server
                            */
                            target.addClass("show");
                            memory.fruit2 = target;

                            if (
                                memory.fruit1.attr("data-fruit") ==
                                memory.fruit2.attr("data-fruit")
                            ) {
                                memory.solution.push({
                                    index1: memory.fruit1.attr("data-index"),
                                    index2: memory.fruit2.attr("data-index"),
                                });
                                memory.fruit1.addClass("found");
                                memory.fruit2.addClass("found");

                                if (memory.solution.length == memory.problem.length / 2) {
                                    clearTimeout(memory.timer);
                                    clearInterval(memory.interval);
                                    showMessage("Bravo !");

                                    axios
                                        .post("/api/game/verify", memory)
                                        .catch(function (error) {
                                            console.log("unable to post solution...");
                                        });

                                }

                                console.log(memory.solution);
                            }
                        }

                        //go to next step : 
                        memory.step = (memory.step + 1) % 2;
                    }
                });
            })
            .catch(function (error) {
                console.log("unable to retrieve game data...");
            });
    }

    /**
     * clean out game state :
     * - clear timeout
     * - clear time bar refreshess interval
     * - unbind click event on each game card
     * - unbind message buttons click event
     */
    function gameCleanout() {
        clearTimeout(memory.timer);
        clearInterval(memory.interval);
        $(".game-board li").unbind("click");
        $(".message__buttons button").unbind("click");
    }

    /**
     * just nest state change functions in an array to easily call them at the right moment
     * i.e : while changing state
     */
    var stateChangeFunctions = {
        menuInit: menuInit,
        menuCleanout: menuCleanout,
        gameInit: gameInit,
        gameCleanout: gameCleanout,
    };


    /**
     * main state change function
     * each time a state is changed, clean the old state and init the new one 
     */
    function setState(newState) {

        //get the current state
        currentState = state;

        //hide views
        $(".view").hide();

        //clean out the current state (if defined)
        if (currentState !== undefined) {
            stateChangeFunctions[currentState + "Cleanout"]();
        }

        //set the new state
        state = newState;

        //init the new state
        stateChangeFunctions[state + "Init"]();

        //show the associated view
        $(".state-" + newState + ".view").show();
    }

    //let's go!
    setState("menu");
});