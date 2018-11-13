
<style>
    .padder{
        padding-top: 25%;
        padding-bottom: 25%;
    }
    .marger{
        margin-top: 15%;
    }
    .row{
        margin: 0 0 0 0;
        width : 100%;
        margin-left: auto;
        margin-right: auto;
    }

    h1 {
        overflow: hidden; /* Ensures the content is not revealed until the animation */
        border-right: .8em solid black; /* The typwriter cursor */
        white-space: nowrap; /* Keeps the content on a single line */
        margin: 0 auto; /* Gives that scrolling effect as the typing happens */
        letter-spacing: .15em; /* Adjust as needed */
        animation:
            typing 3.5s steps(40, end),
            blink-caret .75s step-end infinite;
    }

    /* The typing effect */
    @keyframes typing {
        from { width: 0 }
        to { width: 100% }
    }

    /* The typewriter cursor effect */
    @keyframes blink-caret {
        from, to { border-color: transparent }
        50% { border-color: black; }
    }

</style>

<div class="row marger">
    <div class="text-center padder">
        <h1 class="typewriter">
            <?= $mainTitle; ?>
        </h1>
        <hr>
        <p>
            <?= $content; ?>
        </p>
        <p>
            Php v<?= $phpVersion; ?>
        </p>
    </div>
</div>