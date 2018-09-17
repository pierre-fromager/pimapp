<style>
    .smallTxt{
        font-size: smaller;
        min-height: 20px;
        line-height: 13px;
        padding-top: 4px;
    }
    .leftTxt{
        text-align: left
    }
    .infoLabel{
        font-style: italic;
    }
    .odd{
        background-color: lightgray
    }
    .even{
        background-color: whitesmoke
    }
    .bordered{
        margin-top: 2px;
        border: solid black 1px;
    }
</style>
<?= $nav; ?>
<div class="row">
    <h1 class="col-md-12 text-center">
        <span class="fa fa-bug"></span>
        &nbsp;Oops <?=PHP_VERSION?>!
    </h1>
</div>

<div class="row">
    <div class="col-md-6 bordered">
        <h3>
            <span class="fa fa-dot-circle-o"></span>
            &nbsp;Origin
        </h3>
        <ul>
            <li>Controller : <?= $controller; ?></li>
            <li>Action : <?= $action; ?></li>
        </ul>
    </div>
    <div class="col-md-6 bordered">
        <h3>
            <span class="fa fa-hand-o-down"></span>
            &nbsp;Errors
        </h3>
        <ul>
            <?php
            foreach ($errors as $error) {
                echo '<li>[ ' . $error['code']
                . ' ]&nbsp;:&nbsp;'
                . $error['message'] . '</li>';
            }
            ?>
        </ul>
    </div>
</div>
<div class="row">
    <div class="col-md-12 bordered">
        <h3>
            <span class="fa fa-info-circle"></span>
            &nbsp;Infos
        </h3>
        <div class="col-md-12" style="">
            <?php
            $c = 0;
            foreach ($request->getServer() as $k => $v) {
                ++$c;
                $classColor = ($c % 2 == 0) ? 'odd' : 'even'; ?>
                <div class="col-md-3 smallTxt <?= $classColor ?>">
                    <p class="infoLabel"><b><?= $k; ?></b></p>
                </div>
                <div class="col-md-9 smallTxt leftTxt <?= $classColor ?>">
                    <p><?= $v; ?>&nbsp;</p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>

