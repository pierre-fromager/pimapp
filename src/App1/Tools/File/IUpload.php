<?php

/**
 * App1\Tools\File\IUpload
 */

namespace App1\Tools\File;

interface IUpload
{

    const UPLOAD_ERR_INI_SIZE = 'La taille du fichier dépasse celle autorisée , upload_max_filesize = ';
    const UPLOAD_ERR_FORM_SIZE = 'La taille du fichier est trops importante.';
    const UPLOAD_ERR_NO_TMP_DIR = 'Le paramétrage du répertoire temporaire est incorrecte.';
    const UPLOAD_ERR_CANT_WRITE = 'Échec de l\'écriture du fichier sur le disque.';
    const UPLOAD_ERR_EXTENSION = 'Ce type de fichier n\'est pas autorisé.';
    const UPLOAD_ERR_PARTIAL = 'Le fichier n\'a été que partiellement téléchargé.';
    const UPLOAD_ERR_NO_FILE = 'Aucun fichier n\'a été téléchargé.';
    const UPLOAD_ERR_UNKOWN = 'Erreur inconnue.';
}
