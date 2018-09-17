<?php
$modalTitleId = $modal_id . '_title';
$modalContentId = $modal_id . '_content';
?>
<div id="<?=$modal_id;?>" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 id="<?=$modalTitleId;?>" class="modal-title"><?= $modal_title; ?></h4>
            </div>
            <div class="modal-body">
                <p id="<?=$modalContentId;?>"><?= $modal_content; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <!--<button type="button" class="btn btn-primary">Save changes</button>-->
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
    $j(document).ready(function() {
        $j(".modalLink").click(function(){
            var target = $j(this).attr("data-target");
            $j('#<?=$modal_id;?>').modal('show');
            $j("#<?=$modalContentId?>").html('Loading...');      
            $j.ajax({
                type:"POST"
                , url:target
                , dataType: 'json'
                , error:function(msg){
                    $j("#<?=$modalContentId?>").html('Failed to load content ' + msg);
                  }
                , success:function(data){
                    $j("#<?=$modalTitleId;?>").html(data.title);
                    $j("#<?=$modalContentId?>").html(data.content); 
                } 
            }); 
        });
    });
</script>