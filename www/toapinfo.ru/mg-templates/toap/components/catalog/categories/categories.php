<?php
$categories = MG::get('category')->getHierarchyCategory($data, true);
if (!empty($categories)):
    $total = count($categories);
    ?>
    <section class="mc__assoc-plan">
        <?php foreach ($categories as $index => $category): 
              if (stristr($category['url'], 'kinoklub') !== FALSE) {
                  $category['url'] = str_replace('kinoklub', 'film_club', $category['url']);
                } elseif (stristr($category['url'], 'tavrim') !== FALSE) {
                  $category['url'] = str_replace('tavrim', 'tauride_mysteries', $category['url']);
                } elseif
                 (stristr($category['url'], 'supervizii') !== FALSE) {
                  $category['url'] = str_replace('supervizii', 'supervision_course', $category['url']);
                } 
                elseif (stristr($category['url'], 'seminary') !== FALSE) {
                  $category['url'] = str_replace('seminary', 'seminars', $category['url']);
                }  
                elseif (stristr($category['url'], 'apiubk') !== FALSE) {
                  $category['url'] = str_replace('apiubk', 'basic_course', $category['url']);
                }?>
            <?php if ($index < $total - 2): // первые три элемента ?>
               
                    <a class="assoc-plan__blockA" href="<?php echo SITE.'/'.$category['parent_url'].$category['url']; ?>">
                        <p><?php echo $category['title']; ?></p>
                        <svg class="icon" width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"></path>
                        </svg>
                        <?php if (!empty($category['image_url'])): ?>
                            <img class="foto" src="<?php echo SITE.$category['image_url']; ?>" alt="<?php echo $category['seo_alt'] ? $category['title'] : $category['title'] ?>" title="<?php echo $category['seo_title'] ? $category['title'] : $category['title']  ?>">
                        <?php else: ?>
                            <?php
                            $noImageStub = MG::getSetting('noImageStub');
                            if (!$noImageStub) {
                                $noImageStub = '/uploads/no-img.jpg';
                            }
                            ?>
                            <img class="foto" src="<?php echo SITE.$noImageStub; ?>" alt="<?php echo $category['title']; ?>" title="<?php echo $category['title']; ?>">
                        <?php endif; ?>
                    </a>
              
            <?php endif; ?>
        <?php endforeach; ?>
        
        <div class="blockB">
            <?php foreach ($categories as $index => $category):
               if (stristr($category['url'], 'kinoklub') !== FALSE) {
                  $category['url'] = str_replace('kinoklub', 'film_club', $category['url']);
                } elseif (stristr($category['url'], 'tavrim') !== FALSE) {
                  $category['url'] = str_replace('tavrim', 'tauride_mysteries', $category['url']);
                } elseif
                 (stristr($category['url'], 'supervizii') !== FALSE) {
                  $category['url'] = str_replace('supervizii', 'supervision_course', $category['url']);
                } 
                elseif (stristr($category['url'], 'seminary') !== FALSE) {
                  $category['url'] = str_replace('seminary', 'seminars', $category['url']);
                } 
                elseif (stristr($category['url'], 'apiubk') !== FALSE) {
                  $category['url'] = str_replace('apiubk', 'basic_course', $category['url']);
                }?>
                
                <?php if ($index >= $total - 2): // последние два элемента ?>
                   
                        <a class="assoc-plan__blockB"  href="<?php echo SITE.'/'.$category['parent_url'].$category['url']; ?>">
                            <p><?php echo $category['title']; ?></p>
                            <svg class="icon" width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"></path>
                            </svg>
                            <?php if (!empty($category['image_url'])): ?>
                                <img class="foto" src="<?php echo SITE.$category['image_url']; ?>" alt="<?php echo $category['seo_alt'] ? $category['title'] : $category['title']  ?>" title="<?php echo $category['seo_title'] ? $category['title'] : $category['title'] ?>">
                            <?php else: ?>
                                <img class="foto" src="<?php echo SITE.$noImageStub; ?>" alt="<?php echo $category['title'] ? $category['title'] : $category['title']  ?>" title="<?php echo $category['title'] ? $category['title'] : $category['title']  ?>">
                            <?php endif; ?>
                        </a>
             
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>