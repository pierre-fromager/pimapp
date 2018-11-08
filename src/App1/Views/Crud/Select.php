
<script>

    $(document).ready(function () {

        $j('#field_slot').on('change', function () {
            var redirUrl = ROOT_URL + 'crud/index/slot/' + this.value;

            window.location.replace(redirUrl);
        });

    });


</script>

<br style="clear:both"/>

<div class="row">
    <div class="col-md-12">
        <?= $content; ?>
        <br style="clear:both"/>
    </div>
</div>
<div id="push"></div>