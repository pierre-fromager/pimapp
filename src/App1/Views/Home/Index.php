<style>
    .padder{
        padding-top: 25%;
        padding-bottom: 25%
    }
    .marger{
        margin-top: 10%;
    }
    .row{
        margin: 0 0 0 0;
        width : 100%;
        margin-left: auto;
        margin-right: auto;
    }
    
</style>
<?= $nav; ?>
<br/>
<div class="row marger">
    <div class="well text-center padder">
        <h1>
            <?= $mainTitle; ?>
        </h1>
        <p>
            <?= $content; ?>
        </p>
        <p>
            Php v<?= PHP_VERSION; ?>
        </p>
    </div>
</div>
