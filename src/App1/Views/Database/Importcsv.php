<style>
    .tab-pane{
        word-break:break-all;
        word-wrap: break-word;
    }
    table{
        font-size:0.7em;
    }
    #gauge{
        display:none;
    }
</style>
<script>

    var progress = 0;
    var tablename = '<?= $tablename; ?>';
    var filename = '<?= $filename; ?>';
    var slot = '<?= $slot; ?>';
    var page = <?= $page; ?>;
    var pagesize = <?= $pagesize; ?>;

    function setProgress(percent) {
        progress = percent;
        $j('#percent').text(progress + '%');
        $('#progressbar').attr('aria-valuenow', progress).css('width', progress + '%');
    }

    function callEndPoint() {
        ajaxParams = {
            url: ROOT_URL + "database/asyncimportcsv",
            method: 'POST',
            data: {
                tablename: tablename,
                slot: slot,
                filename: filename,
                page: page,
                pagesize: pagesize
            }
        }
        return $.ajax(ajaxParams);
    }

    function incPage() {
        page = page + 1;
    }

    function startImport() {
        var isValid = tablename.length > 0 && filename.length > 0 && slot.length > 0;
        if (isValid) {
            $.when(callEndPoint()).then(function (data, textStatus, jqXHR) {
                if (jqXHR.status == 200) {
                    var hasDatas = data.datas.length > 0;
                    if (data.linesError.length > 0) {
                        console.error(data.linesError);
                    }
                    if (!hasDatas) {
                        setProgress(100);
                    } else if (data.progress < 100) {
                        setProgress(data.progress);
                        incPage();
                        startImport();
                    }
                }
            });
        }
    }

    $(document).ready(function () {

    <?php if ($ingest) : ?>
            $j('#submit-database-import-csv').click(function (ev) {
            $j('input').css("display", "none");
            $j('select').css("display", "none");
            alert('cococococo');
            });
    <?php endif; ?>
    <?php if ($ingest) : ?>
                $j("#gauge").appendTo("#widget-body");
                $j('input').css("display", "none");
                $j('select').css("display", "none");
                $j('#gauge').css("display", "block");
            startImport();
    <?php endif; ?>
    });
</script>
<?= $nav; ?>
<br style="clear:both"/>
<div class="row">
    <div class="col-md-12">

        <?= $content; ?>
    </div>
</div>


<div class="sk-cube-grid">
    <div class="sk-cube sk-cube1"></div>
    <div class="sk-cube sk-cube2"></div>
    <div class="sk-cube sk-cube3"></div>
    <div class="sk-cube sk-cube4"></div>
    <div class="sk-cube sk-cube5"></div>
    <div class="sk-cube sk-cube6"></div>
    <div class="sk-cube sk-cube7"></div>
    <div class="sk-cube sk-cube8"></div>
    <div class="sk-cube sk-cube9"></div>
</div>

<div id="gauge" class="row col-sm-12">
    <h3>Progress insertions</h3>
    <div class="progress">
        <div id="progressbar" class="progress-bar" role="progressbar" aria-valuenow="0"
             aria-valuemin="0" aria-valuemax="100" style="width:0%">
            <span id="percent">0%</span>
        </div>
    </div> 
</div>