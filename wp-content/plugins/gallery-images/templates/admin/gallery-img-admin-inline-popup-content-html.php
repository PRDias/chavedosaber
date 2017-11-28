<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery('#hugeitgalleryinsert').on('click', function() {
            var id = jQuery('#huge_it_gallery-select option:selected').val();
            window.send_to_editor('[huge_it_gallery id="' + id + '"]');
            tb_remove();
        })
    });
</script>
<div id="huge_it_gallery" style="display:none;">
    <h3><?php echo __('Selecione a galeria de imagens para inserir no post', 'gallery-images'); ?></h3>
    <?php
    global $wpdb;
    $query="SELECT * FROM ".$wpdb->prefix."huge_itgallery_gallerys order by id ASC";
    $shortcodegallerys=$wpdb->get_results($query);
    ?>
    <?php  if (count($shortcodegallerys)) {
        echo "<select id='huge_it_gallery-select'>";
        foreach ($shortcodegallerys as $shortcodegallery) {
            echo "<option value='".$shortcodegallery->id."'>".$shortcodegallery->name."</option>";
        }
        echo "</select>";
        echo "<button class='button primary' id='hugeitgalleryinsert'>Adicionar</button>";
    } else {
        echo " slideshows não encontrado", "huge_it_gallery";
    }
    ?>
</div>