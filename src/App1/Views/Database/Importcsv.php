<style>
    .tab-pane{
        word-break:break-all;
        word-wrap: break-word;
    }
    table{
        font-size:0.7em;
    }
    #progress-step{
        display:none;
    }
    #line-errors{
        word-wrap: break-word;
    }
    #line-errors > p{
        color:red;
        font-size: 10px;
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
        console.log('Import checking');
        var isValid = tablename.length > 0 && filename.length > 0 && slot.length > 0;
        if (isValid) {
            console.log('Import starting');
            $.when(callEndPoint()).then(function (data, textStatus, jqXHR) {
                if (jqXHR.status == 200) {
                    var hasDatas = data.datas.length > 0;
                    if (data.linesError.length > 0) {
                        $j('#line-errors').append(
                                '<p>' + data.linesError.join(' , ') + '</p>'
                                );
                    }
                    if (!hasDatas) {
                        setProgress(100);
                        $j('#spinner').css('display', 'none');
                        stickyFooter();
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


        $j('#submit-database-import-csv').click(function (ev) {
            //$j(this).prop('disabled', true);
        });

    <?php if ($ingest && $isValid) : ?>
                $("section").first().css("display", "none");
                $j('#formDatabase-import-csv').css("display", "none");
                $j('#progress-step').css("display", "block");
                stickyFooter();
                startImport();
    <?php endif; ?>
    });
</script>

<?= $nav; ?>

<br style="clear:both"/>

<div class="row">
    <div class="col-md-12">
        <?= $content; ?>

        <section class="widget" id="progress-step">
            <header>
                <h3>Progress insertions [<i> <?= $tablename; ?> </i>]</h3>
            </header>
            <div class="body">

                <div id="gauge" class="col-sm-12">
                    <div class="progress">
                        <div id="progressbar" class="progress-bar progress-bar-striped bg-success" role="progressbar" aria-valuenow="0"
                             aria-valuemin="0" aria-valuemax="100" style="width:0%">
                            <span id="percent">0%</span>
                        </div>
                    </div> 
                </div>
                <hr>
                <div id="line-errors" class="col-sm-12"></div>

                <div id="spinner" class="sk-cube-grid">
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
            </div>
            <br style="clear:both"/>
        </section>

    </div>
</div>
<div id="push"></div>

