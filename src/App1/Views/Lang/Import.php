<form method="post" action="<?= $baseurl . $import_action; ?>" enctype="multipart/form-data">
    <?= $langSelector; ?>
    <input type="hidden" name="MAX_FILE_SIZE" value="<?= $max_file_size; ?>">
    <input type="file" name="filename">
    <input type="submit" value="Envoyer">
</form>