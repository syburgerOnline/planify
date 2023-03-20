/* jshint esversion: 6 */

/**
 * DRAG functions
 */
function avoidDrag(event) {
    console.log('avoid Drag -> ', event);
    const dragContainer = document.getElementById('editDialog');
    dragContainer.draggable = false;
    const header = dragContainer.getElementsByClassName('dialog-header')[0];
    header.addEventListener('mousedown', allowDrag, true);
}
function allowDrag(event) {
    console.log('allow Drag -> ', event);
    const dragContainer = document.getElementById('editDialog');
    dragContainer.draggable = true;
    const header = dragContainer.getElementsByClassName('dialog-header')[0];
    header.removeEventListener('mousedown', allowDrag, true);
}
let tmpDataTransfer = [];
const dragElements = ['toolbox',
    'editDialog',
    'printDialog',
    'dragItem_addArticle',
    'dragItem_addAd',
    'dragItem_submenu_addAd_0',
    'dragItem_submenu_addAd_1',
    'dragItem_submenu_addAd_2',
    'dragItem_submenu_addAd_3',
    'dragItem_submenu_addAd_4',
    'dragItem_submenu_addAd_5',
    'dragItem_submenu_addAd_6',
    'dragItem_submenu_addAd_7',
    'dragItem_submenu_addAd_8'
]
function drag(event, draggable = true) {
    const isMobile = event.type === "touchstart";
    const target = isMobile ? event.target.parentNode : event.target;
    const isDraggable = dragElements.includes(target.id);


    console.log('dragStart -> ', target, isDraggable);

    if (draggable && isDraggable) {
        document.addEventListener('dragend', dragend, false);
        const style = window.getComputedStyle(target, null);
        const x = isMobile ? event.touches[0].clientX : event.clientX;
        const y = isMobile ? event.touches[0].clientY : event.clientY;
        if(isMobile) {
            dragStartTouch(event, target);
            dragTouchItem = target;
            touchActive = true;
            target.addEventListener("touchmove", dragTouch, false);
            target.addEventListener("touchend", dragend, false);
        }
        const id = target.id;
        const str = (parseInt(style.getPropertyValue("left")) - x) + ',' + (parseInt(style.getPropertyValue("top")) - y) + ',' + id;
        console.log({str});
        if(event.dataTransfer) {
            event.dataTransfer.setData("Text", str);
        } else {
            tmpDataTransfer = [str];
        }
    }
    event.stopPropagation();
    // event.preventDefault();
}
function dragend(event) {

    const isMobile = event.type === "touchend";
    const target = isMobile ? event.target.parentNode : event.target;
    console.log('dragEnd -> ', event, target);
    if(target.id === 'toolbox' || target.id === 'editDialog' || target.id === 'printDialog') {
        const x = document.getElementById(target.id).offsetLeft; // getOffset(document.getElementById(target.id)).left;
        const y = document.getElementById(target.id).offsetTop; // getOffset(document.getElementById(target.id)).top;
        setCookie(target.id, [x, y], 30);
        setDialogQuotient(target.id);
    }
    if(isMobile) {
        target.removeEventListener("touchmove", dragTouch, false);
        target.removeEventListener("touchend", dragend, false);
        touchActive = false;
    }
    document.removeEventListener('dragend', dragend, false);
}
function getDropTarget(event) {
    return event.target.parentNode.parentNode;
}
function replaceDragItem () {
    pageHitTest = false;
}
function placeDragItem() {

}
function drop(event) {
    event.stopPropagation();
    event.preventDefault();
    const dropTarget = getDropTarget(event);
    if(dropTarget.id.substr(0,5) === 'theme' && this.dragItem) {
        checkDroppedItems(this.dragItem, dropTarget);
        // placeDragItem();
        console.log('drop inner -> ', event, dropTarget, this.dragItem, this.dragItem.scope);
    } else {
        replaceDragItem();
        console.log('drop outer -> ', event, dropTarget);
    }
    let offset;
    if(event.dataTransfer) {
        offset = event.dataTransfer.getData("Text").split(',');
    }else {
        offset = tmpDataTransfer.split(',');
    }

    const dm = document.getElementById(offset[2]);
    dm.style.left = (event.clientX + parseInt(offset[0], 10)) + 'px';
    dm.style.top = (event.clientY + parseInt(offset[1], 10)) + 'px';
    event.preventDefault();
    return false;
}

function allowDrop(event) {
    event.preventDefault();
    return false;
}
let touchActive = false;
let initialX;
let initialY;
let currentX;
let currentY;
let xOffset = 0;
let yOffset = 0;
let dragTouchItem;
function dragStartTouch(e, target) {

    if (e.type === "touchstart") {
        initialX = e.touches[0].clientX - xOffset;
        initialY = e.touches[0].clientY - yOffset;
    } else {
        initialX = e.clientX - xOffset;
        initialY = e.clientY - yOffset;
    }
    console.log('dragStart aus touch-> ', initialX, initialY);

    if (target === dragTouchItem) {
        touchActive = true;
    }
}
function dragEndTouch(e) {
    initialX = currentX;
    initialY = currentY;

    touchActive = false;
}
function dragTouch(e) {
    if (touchActive) {

        e.preventDefault();

        if (e.type === "touchmove") {
            console.log('x -> ', e.touches[0].clientX, 'x -> ', e.touches[0].clientY);
            currentX = e.touches[0].clientX; //  - initialX;
            currentY = e.touches[0].clientY; //  - initialY;
        } else {
            currentX = e.clientX - initialX;
            currentY = e.clientY - initialY;
        }

        xOffset = currentX;
        yOffset = currentY;

        setTranslateTouch(currentX, currentY, dragTouchItem);
    }
}
function setTranslateTouch(xPos, yPos, el) {
    el.style.left = xPos + "px";
    el.style.top = yPos + "px";
    // el.style.transform = "translate3d(" + xPos + "px, " + yPos + "px, 0)";
}
function convertTouchEvents() {
    /*
    const container = document.getElementById('container');
    console.log('convertTouchEvents -> ', container, ' has drag properties ');
    container.addEventListener("touchstart", dragStartTouch, false);
    container.addEventListener("touchend", dragEndTouch, false);
    container.addEventListener("touchmove", dragTouch, false);

    container.addEventListener("mousedown", dragStartTouch, false);
    container.addEventListener("mouseup", dragEndTouch, false);
    container.addEventListener("mousemove", dragTouch, false);
    */

    const container = document.getElementById('container').childNodes;
    for(let i in container) {
        const element = container[i];
        if(element.ondragstart){
            // (event) => dragItemDragstart(event)
            // console.log('check for items in Container -> ', element, ' has drag properties ');
            // element.addEventListener("touchstart", drag, true);
            // element.addEventListener("touchend", dragend, true);
            // element.addEventListener("touchmove", drag, true);
            /*
            element.addEventListener("touchstart", (event) => drag(event));
            element.addEventListener("touchend", dragend, false);
            element.addEventListener("touchcancel", dragend, false);
            element.addEventListener("touchleave", dragend, false);
            element.addEventListener("touchmove",  (event) => drag(event));

             */
        }
    }
}
/**
 * Dragend
 */