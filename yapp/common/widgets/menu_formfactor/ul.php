
<li <?php if ($item['id'] == $this->currentItem ) {echo ' class="active"';}?>><a href="<?= $item['link'] ?>"><?= $item['name'] ?><?php if (isset($item['childs'])) : ?>
        <?php endif; ?></a><?php if (isset($item['childs'])) : ?>

        <ul>
            <?= $this->getMenuHtml($item['childs']) ?>
        </ul>
    <?php endif; ?></li>
