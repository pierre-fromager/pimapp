<style>
    p::before {
        display: inline-block;
        font: normal normal normal 14px/1 FontAwesome;
        font-size: inherit;
        text-rendering: auto;
        -webkit-font-smoothing: antialiased;
    }
    
    #label_login::before{
        content: "\f0e0\00a0";
    }
    #label_password::before{
        content: "\f023\00a0";
    }
    div#login{
       /* margin-top: 33%;*/
    }
</style>
<div id="login" class="row">
    <div class="col-md-offset-2 col-md-8">
        <div class="center-block">

            <?= $form; ?>
        </div>
    </div>
</div>