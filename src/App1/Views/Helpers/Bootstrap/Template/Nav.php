<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbarCollapse" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?= $title['link']; ?>">
                <span class="fa <?= $title['icon']; ?>">&nbsp;</span>
                <?= $title['text']; ?>
            </a>
        </div>
        <div class="navbar-collapse collapse" id="navbarCollapse" aria-expanded="false" style="height: 1px;">
            <ul class="nav navbar-nav navbar">
                <?php foreach ($items as $item) : ?>
                    <li>
                        <a title="<?= $item['title'] ?>" href="<?= $item['link'] ?>">
                            <span class="<?= $item['icon'] ?>"></span>
                            <?= $item['title'] ?>
                        </a>
                        </li>
                <?php endforeach; ?>                  
            </ul>
        </div>
    </div>
</nav>