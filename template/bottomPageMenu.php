<?php
 function createBottomPageMenu(): string
 {
     $dialog = 'editDialog';
     $saveForm = 'mainForm';
     // open action defines dialog, openaction and scope of dialog
     $openAction = "openClose('editDialog', 'open', 'addArticle', 'Artikel erstellen')";
     $saveAction = 'save('.$saveForm.')';
     return '<div class="interactive-menu">'.
            '<i class="fa-solid fa-file-circle-plus add-icon" onclick="'.$openAction.'"></i>'.
            '<i class="fa-solid fa-floppy-disk save-icon" onclick="'.$saveAction.'"></i>'.
         '</div>';
 }