<?php
mgSEO($data);
?>

<div class="c-product product-details-block">
                <?php if (class_exists('BreadCrumbs')): ?>
                    [brcr]
                <?php endif; ?>
 <div class="main__cont mc max-cont-width">
    <section class="mc__title">
    <h1>
        <?php echo $data['title'] ?>
    </h1>
</section>
[buy-click id="<?php echo $data['id']?>" count="<?php echo $data['count']?>"]
    <section class="mc__about-project">
    <div class="mc__about-project_cont">
        <h2>
            О проекте
        </h2>

        <p>
            <?php echo $data['description']?>
        </p>
    </div>
   <div class="l-col min-0--12 min-768--6">
                        <?php
                        // Карусель изображений товара
                        component(
                            'product/images',
                            $data
                        );
                        ?>
                    </div>
</section>
</div>


</div>
