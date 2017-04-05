<?php
    $settingData = $step->getSettingData();
    $selectedCategoryIds = (isset($settingData['category_ids'])) ? $settingData['category_ids']  : [];
?>
<?php $this->pageTitle = $step->title . ' - ' . Yii::app()->name; ?>
<h2 class="page-header"><?php echo Yii::t('frontend', 'Migrate Settings');?> > <?php echo Yii::t('frontend', $step->title); ?> </h2>
<form role="form" method="post" action="<?php echo UBMigrate::getSettingUrl($step->sorder); ?>">
    <div id="step-content">
        <blockquote> <p class="tip"> <?php echo Yii::t('frontend', $step->descriptions); ?> </p> </blockquote>
        <ul class="list-group">
            <li class="list-group-item">
                <h3 class="list-group-item-heading">
                    <?php echo Yii::t('frontend', 'Total Product Categories'); ?> (<?php echo $totalCategories; ?>)
                </h3>
                <?php
                $maxVars = (int)ini_get('max_input_vars');
                if ($totalCategories > $maxVars) {
                    echo '<p>(<mark>'.Yii::t('frontend', 'The migration tool detects that you have %s1 Product Categories. Please increase the max_input_vars param in your PHP settings (New value must be bigger than %s2) before continuing this step.', array("%s1" => $totalCategories, "%s2" => $totalCategories)).'</mark>)</p>';
                }
                ?>
                <input type="checkbox" <?php echo ($totalCategories == sizeof($selectedCategoryIds)) ? "checked" : ''; ?> id="select_all_categories" name="select_all_categories" value="1" />
                <label title="<?php echo Yii::t('frontend', 'Click here to select all categories');?>" for="select_all_categories"> <?php echo Yii::t('frontend', 'Select All');?> </label>
                <?php if ($rootCategories): ?>
                    <?php foreach ($rootCategories as $rootCategory):?>
                        <?php
                            //check has selected
                            $checked = in_array($rootCategory->entity_id, $selectedCategoryIds) ? true : false;
                            //check has migrated
                            $m2Id = UBMigrate::getM2EntityId(4, $rootCategory->tableName(), $rootCategory->entity_id);
                            //get child categories of this category
                            $categoryTree = UBMigrate::getMage1CategoryTree($rootCategory->entity_id);
                        ?>
                        <div class="tree well">
                            <?php if ($m2Id): ?>
                                <span class="glyphicon glyphicon-ok-sign text-success"></span>
                            <?php endif; ?>
                            <input type="checkbox" <?php echo ($checked) ? "checked" : ''; ?> id="category_<?php echo $rootCategory->entity_id; ?>" name="category_ids[]" value="<?php echo $rootCategory->entity_id; ?>" />
                            <span class="root-category" title="<?php echo Yii::t('frontend', 'Click here to show/hide child categories'); ?>"><?php echo UBMigrate::getMage1CategoryName($rootCategory->entity_id); ?> (<span style="font-weight: normal;"><?php echo Yii::t('frontend', 'root category');?></span>)</span>
                            <span class="head-tip">(<?php echo Yii::t('frontend', 'Click the category\'s name to show/hide child categories')?>)</span>
                            <?php
                                if ($categoryTree) {
                                    echo UBMigrate::generateCategoryTreeHtml($categoryTree, $selectedCategoryIds, 1);
                                }
                            ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </li>
        </ul>
        <?php $this->renderPartial('/base/_buttons', array('step' => $step)); ?>
    </div>
</form>
<script type="text/javascript">
    //for category tree
    (function($){
        $('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');
        $('.tree li.parent_li > span').on('click', function (e) {
            var children = $(this).parent('li.parent_li').find(' > ul > li');
            if (children.is(":visible")) {
                children.hide('fast');
                $(this).attr('title', 'Expand this branch').find(' > i').addClass('icon-plus-sign').removeClass('icon-minus-sign');
            } else {
                children.show('fast');
                $(this).attr('title', 'Collapse this branch').find(' > i').addClass('icon-minus-sign').removeClass('icon-plus-sign');
            }
            e.stopPropagation();
        });
        //show/hide root block
        $('.tree span.root-category').on('click', function(){
            var children = $(this).siblings('ul');
            if (children.is(":visible")) {
                children.hide('fast');
            } else {
                children.show('fast');
            }
        });
        $('.tree span.root-category').trigger('click');

        //check/un-check
        $('.tree INPUT[name="category_ids[]"]').on('change', function(){
            var value = this.checked;
            //update children status
            var $children = $(this).siblings('ul');
            if ($children.length){
                $children.children('li').each(function(i){
                    $(this).find('input').prop("checked", value);
                });
            }
            //update parent status
            var $parent = $(this).parent().parent().siblings('input');
            if ($parent.length && value){ //if checked
                if (!$parent.prop("checked")){
                    $parent.prop("checked", value);
                }
            }
        });
        $('INPUT[name="select_all_categories"]').on('change', function(){
            var value = this.checked;
            $('.tree INPUT[name="category_ids[]"]').prop("checked", value);
            $('.tree INPUT[name="category_ids[]"]').trigger('change');
            //show/hide child block
            /*if (this.checked) {
                $(this).siblings("div.tree.well").hide(100);
            } else {
                $(this).siblings("div.tree.well").show(200);
            }*/
        }).trigger('change');

    })(jQuery);
</script>