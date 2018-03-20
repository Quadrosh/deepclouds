
<li <?php if ($item['id'] == $this->currentItem || $item['id'] == $this->parentItem) {echo ' class="active"';}?>><a href="<?= $item['link'] ?>"><?= $item['name'] ?><?php if (isset($item['childs'])) : ?>
        <?php endif; ?></a><?php if (isset($item['childs'])) : ?>
        <a data-toggle="collapse" href="#collapse<?= $this->collapseId ?>"><span  class="accordion_toggle pull-right"><i class="glyphicon glyphicon-chevron-down"></i></span></a></li>
        <ul id="collapse<?= $this->collapseId ?>" class=" collapse <?php $this->collapseId++; if ($item['id'] == $this->parentItem || $item['id'] == $this->currentItem) {echo 'in';} ?>">
            <?= $this->getMenuHtml($item['childs']) ?>
        </ul>
    <?php else : ?></li>
    <?php endif; ?>
