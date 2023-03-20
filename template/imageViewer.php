<?php
function createImageViewer():void
{
    echo '<div class="layer-close" id="overLayerClose">
                <div class="layer-close-btn" id="overLayerCloseBtn">X</div>
            </div>
            <!-- iFrame solution --> 
            <!--
            <iframe class="pdf-reader" id="pdfReader" src="" scrolling="yes" style="width:100%;height:100%;border:none" tc-textcontent="true" data-tc-id="w-0.7617171970856862" type="application/pdf">
            </iframe>
            -->
            <!-- OBJECT solution -->
            <object class="pdf-reader" id="pdfReader" data="" type="application/pdf" width="100%" height="100%" type="application/pdf">
                alt : <a href="" id="pdfReaderAltTag"></a>
            </object>
            <!-- EMBED solution -->
            <!--
            <embed class="pdf-reader" id="pdfReader" src="" type="application/pdf">
            -->
            <!-- for mobile clients do not touch-->
            <form class="pdf-form" id="pdfForm" method="post" action="pdf.php" style="display: none;">
            <input type="text" id="pdfLink" name="pdfLink">
            </form>
            <!-- width="100%" height="100%" -->
            <!--<embed class="pdf-reader" id="pdfReader" src="">-->
            <!--
            <object class="pdf-reader" id="pdfReader" data="" type="application/pdf" width="100%" height="100%">
                alt : <a href="" id="pdfReaderAltTag"></a>
            </object>-->';
}
