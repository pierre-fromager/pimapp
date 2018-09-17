<div id="fullcarousel-example" data-interval="<?=$interval;?>" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner" style="margin-left: auto;margin-right: auto;width: 100%">
        <?php
        $itemCount = 0;
        foreach ($items as $item) {
            $active = ($itemCount == 0) ? 'active' : '';
            echo '<div class="item ' . $active . '">'
                . '<img class="img-responsive" src="' . $item['image'] . '">'
                . '<div class="carousel-caption">'
                . '<h2>' . $item['title'] . '</h2>'
                . '<p>' . $item['description'] . '</p>'
                . '</div>'
                . '</div>';
            ++$itemCount;
        }
        ?>
        <a class="left carousel-control" href="#fullcarousel-example" data-slide="prev">
            <i class="icon-prev fa fa-angle-left"></i>
        </a>
        <a class="right carousel-control" href="#fullcarousel-example" data-slide="next">
            <i class="icon-next fa fa-angle-right"></i>
        </a>
    </div>
</div>