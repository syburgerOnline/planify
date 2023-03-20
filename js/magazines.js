/* jshint esversion: 6 */

/*
import { touchHandle }  from "/touchhandle.js";
console.log(touchHandle(10));

 */

// import PDFObject from "./pdfobject";

let pageHitTest = false;
let dragArticleItemsInitiated = false;
let dragAdItemsInitiated = false;
let visibleForm = '';
let openDialogElement;
let toolboxQuotient;
let editDialogQuotient;
let printDialogQuotient;
let parentStageWidth;
let parentStageRefX;
let activeLineBreakClipArray = [];
let prevActiveLineBreakClipArray = [];
let windowResized = false;

const pageArray = [];
const adInfoArray = [['Anzeige: ','','']];
const articleInfoArray = [['Artikel: ','','']];

const textFieldInitialValue = 'calc(100% - 10px)';
const notificationArray = [];
// TODO this is just a workaround till we get articleStatusId's
const articleStatusIds = ['planned', 'data-arrived', 'layout-ready', 'layout-in-progress', 'layout-arrived','layout-review', 'layout-final'];
const articleStatusTranslations = ['geplant', 'Artikel abgegeben', 'Bereit für Layout', 'Layout in Arbeit', 'Layout vorhanden','Layout in Korrektur', 'Layout freigegeben'];
const magazineArray = ['', 'Tourenfahrer', 'MOTORRAD NEWS', 'Motorrad ABENTEUER', 'Motorrad-GESPANNE','Palette', 'bike & business', 'MOTORRÄDER der Jahreskatalog'];

// initializing formData
let formDefined = false;

// used in resize for articles
let m_pos;
let scaleTarget;

// setting positions initializing
const themesArray = [];
let timeout

// submenu
let visibleSubMenu = '';

let maxArticleWidth = 1920; // document.getElementById('stage').offsetWidth;
let minArticleWidth = 50;
let activeTitleTextField = '';
const adBorderStyle = '1px solid black';

// Gesamtstatus:
let articleAmount = 0;
let pageAmount = 0;
let adAmount = 0;
let articlePercentageQuotient = 0;
let testStr = '';
let debugCount = 0;
let isMobile = false;
function debug(value) {

    const textArea = document.getElementById('testTextContainer');
    const testClearButton = document.getElementById('testClearButton');
    if(!textArea) {
        console.log(value);
        return;
    }
    textArea.classList.add('z-top');
    textArea.clear = function() {
        this.classList.remove('z-top');
        testStr = '';
        debugCount = 0;
        // const textArea = document.getElementById('testTextContainer');
        this.value = '';
    }
    testClearButton.textArea = textArea;
    testClearButton.onclick = function() {
        this.textArea.clear();
    }

    if(typeof value == 'object') {
        let counta = 0;
        for(let i in value) {
            const substring = value[i];
            if(typeof substring == 'object') {
                testStr += '[';
                let countb = 0;
                for(let z in substring) {
                    testStr += z + ': ';
                    testStr += substring[z] + ' ';
                    if(countb < substring.length - 1) {
                        testStr += ' -> ';
                        countb ++;
                    } else {
                        testStr += "\n";
                    }
                }
                testStr += ']';
            } else {
                testStr += substring;
            }
            if(counta < value.length - 1) {
                testStr += ' -> ';
                counta ++;
            } else {
                testStr += "\n";
            }
        }
    } else {
        testStr = value + "\n";
    }
    console.log(testStr, value);
    debugCount ++;
    const count = debugCount + "\n";
    textArea.value += 'new debug: ' + count + testStr + "\n";
}

function setCookie(name, value, days) {
    let expires;
    if (days) {
        const date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        expires = "; expires="+date.toUTCString();
    }
    else expires = "";
    const cookieString = name+"="+value+expires+"; path=/; SameSite=None; Secure";
    // console.log('setCookie -> ', cookieString);
    document.cookie = cookieString;
}
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

function freezeStage(clip) {
    // console.log('freeze',clip, activeLineBreakClipArray);
    activeLineBreakClipArray = [];
    for(let i = 1; i < clip.id; i++) {
        const clip = document.getElementById('theme_' + i);
        const x = clip.offsetLeft;
        const y = clip.offsetTop;
        const rightNeighbour = document.getElementById('theme_' + (i + 1));
        if(rightNeighbour.offsetTop > y+5) {
            const articleContainer = document.getElementById('theme-content_' + i);
            if(articleContainer.style.display==='block') {
                const articleWidth = articleContainer.offsetWidth - 2;
                articleContainer.style.width = articleWidth + 'px';
            }
            const activeLineBreakClip = {clip: clip, id: getId(clip), left: x, top: y, article: articleContainer};
            activeLineBreakClipArray.push(activeLineBreakClip);
        }
    }
    // console.log({activeLineBreakClipArray});
}
function releaseStage(stage) {
    // console.log('release', stage);
    if(activeLineBreakClipArray.length > 0) {
        prevActiveLineBreakClipArray = activeLineBreakClipArray;
        /*
        if(activeLineBreakClipArray[0].id > prevActiveLineBreakClipArray[0].id) {
            prevActiveLineBreakClipArray = activeLineBreakClipArray;
        } else {
            prevActiveLineBreakClipArray = activeLineBreakClipArray;
        }
        */
    }
    // stage.style.paddingLeft = '';
    // stage.style.overflowX = 'visible';
    windowResized = false;
    /*
    stage.style.overflowX = 'initial';
    stage.style.width = '100%';
    stage.style.left = '0px';
    resizeActiveLineBreakClips(activeLineBreakClipArray, 'initial');
    activeLineBreakClipArray = [];

     */
}
function setStageSize(value, clip=null) {
    const nav = document.getElementById('navigation');
    const stage = document.getElementById('stage');
    const footer = document.getElementById('footer');
    const w = stage.offsetWidth-1;
    if(clip && parentStageWidth != w) {
        freezeStage(clip);
        parentStageWidth = w;
        parentStageRefX = clip.x;
        stage.style.width = w + 'px';
        stage.style.overflowX = 'scroll';
        stage.style.overflowY = 'visible';
        // stage.style.paddingLeft = '1px';
    } else {
        releaseStage(stage);
    }
}

function getInputField (field) {
    let newField = null;
    if(document.getElementById(field)) {
        newField = document.getElementById(field);
    }
    return newField;
}
function getElement (elementId) {
    let newElement = null;
    if(document.getElementById(elementId)) {
        newElement = document.getElementById(elementId);
    }
    return newElement;
}
function setDialogHeader (clip, text) {
    // console.log('dialogHeader -> ', clip);
    const dialog = document.getElementById(clip);
    const header = dialog.getElementsByClassName('dialog-header-headline')[0];
    header.value = text;
}

function editTheme(id) {

    const dragNameActive = 'dragEditArticleItem_active_' + id;
    const dragNamePassive = 'dragEditArticleItem_passive_' + id;
    const editArticleDummyActive = document.getElementById(dragNameActive);
    const editArticleDummyPassive = document.getElementById(dragNamePassive);

    // console.log('editTheme -> ', id, editArticleDummyActive, editArticleDummyPassive);
}
function getSetIssueYear(year = null) {
    let newYear = year ? year : null;
    let issue = 0;
    const form = document.getElementById('navigationForm');
    const selectElement = form.getElementsByTagName('select')[1];
    const optionsElements = selectElement.getElementsByTagName('option');
    for (let i in optionsElements) {
        const element = optionsElements[i];
        if(newYear) {
            if(element.attributes && element.attributes.year){
                element.selected = true;
                newYear = Number(element.attributes.year.value);
                issue = Number(element.value);
            } else {
                element.selected = false;
            }
        } else {
            if(element.selected) {
                newYear = element.attributes && element.attributes.year ? Number(element.attributes.year.value) : 0;
                issue = Number(element.value);
                break;
            }
        }

    }
    // console.log('getSetIssueYear -> ', newYear, issue, optionsElements);
    return {year: newYear, issue: issue};
}
function getSetMagazine(magazine = null) {
    let newMagazine = magazine ? magazine : null;
    // let issue = 0;
    const form = document.getElementById('navigationForm');
    const selectElement = form.getElementsByTagName('select')[0];
    const optionsElements = selectElement.getElementsByTagName('option');
    for (let i in optionsElements) {
        const element = optionsElements[i];
        if(newMagazine) {
            if(element.value == newMagazine){
                element.selected = true;
                newMagazine = Number(element.value);
            } else {
                element.selected = false;
            }
        } else {
            if(element.selected) {
                newMagazine = element.attributes ? Number(element.value) : 0;
                break;
            }
        }
    }
    // console.log('getSetIssueYear -> ', newYear, issue, optionsElements);
    return {magazine: newMagazine};
}
function getSetIssue(issue = null) {
    let newIssue = issue ? issue : null;
    let newYear = 0;
    const form = document.getElementById('navigationForm');
    const selectElement = form.getElementsByTagName('select')[1];
    if(selectElement){
        const optionsElements = selectElement.getElementsByTagName('option');
        for (let i in optionsElements) {
            const element = optionsElements[i];
            if(newIssue) {
                if(element.selected) {
                    newYear = element.attributes.year.value ? Number(element.attributes.year.value) : 0;
                    newIssue = Number(element.value);
                    break;
                }
            } else {
                if(element.value === newIssue) {
                    element.selected = true;
                    newIssue = Number(element.value);
                    // break;
                } else {
                    element.selected = false;
                }
            }

        }
    }

    // console.log('getSetIssue -> ', newYear, newIssue, optionsElements);
    return {year: newYear, issue: newIssue};
}
function changeMagazine(id, selectObj = {}) {

    const newIssue = getSetIssue(0);
    const year = newIssue.year;
    const issue = newIssue.issue;
    const txt = document.getElementById('actualIssueYear');
    const form = document.getElementById('navigationForm');
    const formData = new FormData(form);
    if(txt) {
        txt.innerHtml = year;
        txt.value = year;
    }


    form.submit();
    // console.log('changeMagazine -> ', id, year, issue, formData);
}
function changeIssue(id, year) {
    let actualYear = year;
    if (year === 'null') {
        actualYear = getSetIssueYear().year;
    }
    const txt = document.getElementById('actualIssueYear');
    const form = document.getElementById('navigationForm');
    const formData = new FormData(form);

    txt.innerHtml = actualYear;
    txt.value = actualYear;
    formData.append('actualYear', actualYear);
    form.submit();
    // console.log('changeIssue -> ', id, actualYear, formData);
}

function initDragElements(dragName, scope) {
    this.dragItem = document.getElementById(dragName);
    setIconVisibility(this.dragItem, 0);
    this.dragItem.tl = this;
    this.draggedItemId = dragName;
    this.dragItem.style.display = 'block';
    this.dragItem.style.position = 'absolute';
    this.dragItem.style.transition = 'all 0s ease-in-out';
    this.dragItem.origX = this.dragItem.offsetLeft;
    this.dragItem.origY = this.dragItem.offsetTop;
    const check = this.dragItem;
    // console.log({check});
    if (scope == 'article') {
        dragArticleItemsInitiated = true;
        this.dragItem.scope = 'article';
        initDragArticleItems(this.dragItem);
    } else if(scope == 'ad') {
        dragAdItemsInitiated = true;
        this.dragItem.scope = 'ad';
        this.dragItem.adValue = document.getElementById('advalue_' + getId(this.dragItem)).value;
        this.dragItem.adClass = document.getElementById('adclass_' + getId(this.dragItem)).value;
        initDragArticleItems(this.dragItem);
    }
}
function getContentType(element) {
    return element.id.substr(0,element.id.indexOf('-'));
}
function getClassesValues(classValues) {
    const tmpArray = [];
    for (const value of classValues) {
        tmpArray.push(value);
    }
    return tmpArray;
}

function setStatusInArray(container, handler, lastClass, value) {
    if(container.inArticlePages.length > 1) {
        const array = container.inArticlePages;
        for (let i in array) {
            if (i > 0) {
                const item = array[i];
                item.articleStatus = value;
                const itemStatusBar = document.getElementById('articleStatus_' + getId(item));
                itemStatusBar.classList.remove(lastClass);
                itemStatusBar.classList.add(value);

                const childId = Array.from(handler.parentNode.children).indexOf(handler);
                itemStatusBar.firstChild.firstChild.childNodes[childId].selected = true;
            }
        }
    }
}

function resetStatusValue(item, value){
    /*
    item.articleStatus = value;
    if(item.inArticlePages.length > 1) {
        const array = item.inArticlePages;

    }
     */
}
function getSelectedItem (event) {

    const options = event.target.getElementsByTagName('OPTION');
    let handler;
    let value;
    for(let i in options){
        if(options[i].selected) {
            handler = options[i];
            value = options[i].value.split(', ')[1].replaceAll("'","");
            break;
        }
    }

    return {handler,value}
}
function setStatusValue(handler, value, id) {
    if(!handler && !value) {
        handler = getSelectedItem(event).handler;
        value = getSelectedItem(event).value;
    }
    const statusBar = document.getElementById('articleStatus_' + id); // handler.parentNode.parentNode.parentNode;
    const container = document.getElementById('theme-content_' + id); // statusBar.parentNode;
    const pageContainer = document.getElementById('theme_' + id); // container.parentNode;
    const lastClass = getClassesValues(statusBar.classList.values()).pop();
    if (getContentType(container) === 'ad') {
        pageContainer.adStatus = value;
    } else if(getContentType(container) === 'theme') {
        pageContainer.articleStatus = value;
        setStatusInArray(pageContainer, handler, lastClass, value);
    }
    statusBar.classList.remove(lastClass);
    statusBar.classList.add(value);
    const refStatusBar = pageContainer.content.referenceClip.content.statusBar.parentNode;
    // console.log('referenceClipStatusBar = ', refStatusBar, 'lastClass = ', lastClass, ' new Value -> ', value);
    if(statusBar && statusBar != refStatusBar) {
        if (getContentType(container) === 'ad') {
            document.getElementById('theme_' + getId(refStatusBar)).adStatus = value;
        } else if(getContentType(container) === 'theme') {
            document.getElementById('theme_' + getId(refStatusBar)).articleStatus = value;
        }

        refStatusBar.classList.remove(lastClass);
        refStatusBar.classList.add(value);
    }
    // console.log('selectStatusValue -> ', statusBar, value, getContentType(container));
    calcPagePercentage();
}
function selectAdType(id) {
    const name = 'addAd_'+ id;
    // console.log('selectAdType mouseOver -> ', name);
    const dragName = 'dragItem_submenu_' + name;
    initDragElements(dragName,'ad');
}
function selectArticle(id) {
    // console.log('selectArticle -> ', id);
}

function selectIssue(issue) {

    if(getInputField('issueNumber')) {
        getInputField('issueNumber').value = issue;
    }
    // console.log('selectIssue -> ', issue, getInputField('issueNumber'));
}

function selectEditUser(handler, email, name , permissions = {}, textFieldName,textFieldEmail, id) {
    getElement(textFieldName).value = name;
    getElement(textFieldEmail).value = email;
    getElement('editUserPermissionsSelectArea').getElementsByTagName('SELECT');
    const selectElements = getElement('editUserPermissionsSelectArea').getElementsByTagName('SELECT');
    // getElement(textFieldEmail).value = email;
    // getElement(textFieldPassword).value = pw;
    for(let i in selectElements){
        const magazineId = Number(i);
        const permissionStr = 'userPermission_' + (magazineId + 1);
        const permission = Number(permissions[permissionStr]);
        const element = selectElements[i];

        if(element.nodeName === 'SELECT') {
            const options = element.getElementsByTagName('OPTION');
            for(let z in options) {
                const option = options[z];

                if(option.nodeName === 'OPTION') {
                    // console.log('optionElement -> ', option, option.value, permission, permissionStr, magazineId);
                    if (Number(option.value) === permission) {
                        option.selected = true;
                    } else {
                        option.selected = false;
                    }
                }

            }
            // console.log('selectedElement -> ', element, permission);
        }
    }
    // console.log('selectEditUser -> ', handler, email, name , permissions, textFieldName,textFieldEmail, id, selectElements);
}

function selectYear(year) {
    if(getInputField('issueYear')) {
        getInputField('issueYear').value = year;
    }
    // console.log('selectYear -> ', year, getInputField('issueYear'));
}
function selectPageAmount(value) {
    if(getInputField('issuePageAmount')) {
        getInputField('issuePageAmount').value = value;
    }
    // console.log('selectPageAmount -> ', value, getInputField('issuePageAmount'));
}
function startDragItem(clip) {

}
function getElementPosition(item) {
    // const element = document.getElementById(item);
    const topPos = item.getBoundingClientRect().top + window.scrollY;
    const leftPos = item.getBoundingClientRect().left + window.scrollX;
    const rightPos = item.getBoundingClientRect().right + window.scrollX;
    const bottomPos = item.getBoundingClientRect().bottom + window.scrollY;
    const widthValue = rightPos - leftPos;
    const heightValue = bottomPos - topPos;
    return {x: leftPos, y: topPos, right: rightPos, bottom: bottomPos, width: widthValue, height: heightValue};
}
function getOffset( el ) {
    let _x = 0;
    let _x2 = 0;
    let _y = 0;
    let _y2 = 0;
    while( el && !isNaN( el.offsetLeft ) && !isNaN( el.offsetTop ) && !isNaN( el.offsetWidth ) && !isNaN( el.offsetHeight ) ) {
        _x += el.offsetLeft - el.scrollLeft;
        _x2 += el.getBoundingClientRect().width;
        _y += el.offsetTop - el.scrollTop;
        _y2 += el.getBoundingClientRect().bottom;
        el = el.offsetParent;
    }
    let h = _x2 - _x;
    let w = _y2 - _y;
    return { top: _y, left: _x, bottom: _y2 , right: _x2, height: h, width: w};
}
function isEven (number) {
    if (number % 2 == 0) {
        return true;
    } else {
        return false;
    }
};
function swapZIndex(id, handler){
    const adContainer = handler.parentNode;
    showInfoPanel(id,adContainer);
    if (!adContainer.zIndex) {
        adContainer.zIndex = adContainer.style.zIndex;
    }
    adContainer.classList.add('z-top');
    if(adContainer.parentNode.hasNeighbour) {
        adContainer.parentNode.neighbour.classList.add('z-top');
    }
}
function swapZIndexBack(id, handler){
    const adContainer = handler.parentNode;
    hideInfoPanel(id,adContainer);
    adContainer.classList.remove('z-top');
    if(adContainer.parentNode.hasNeighbour) {
        adContainer.parentNode.neighbour.classList.remove('z-top');
    }
}
function showInfoPanel(id, handler) {

    const itemContainer = handler.parentNode;
    const handlerType = handler.id.substr(0,handler.id.indexOf('-'));

    const infoPanel = document.getElementById('infoPanel');
    const infoPanelHead = document.getElementById('infoPanelHead');
    const infoPanelTextArea = document.getElementById('infoPanelCopy');
    infoPanelTextArea.style.height = '1px';
    infoPanel.style.display = 'grid';
    if(handlerType === 'theme') {
        infoPanelTextArea.value = articleInfoArray[itemContainer.articleId][0];
        infoPanelHead.innerHTML = articleInfoArray[0][0];
    } else {
        infoPanelTextArea.value = adInfoArray[id][0];
        infoPanelHead.innerHTML = adInfoArray[0][0];
    }
    infoPanelTextArea.style.height = infoPanelTextArea.scrollHeight + 'px';


    const left = getOffset(handler).left + handler.offsetWidth;
    let top;
    if(handlerType === 'theme') {
        top = getOffset(handler).top - (1.5*infoPanel.offsetHeight);
    } else {
        top = getOffset(handler).top - infoPanel.offsetHeight;
    }

    infoPanel.style.left = left + 'px';
    infoPanel.style.top = top + 'px';
    infoPanel.style.opacity = 1;
    clearTimeout(timeout);
}
function hideInfoPanelTransitionEnd() {
    const infoPanel = document.getElementById('infoPanel');
    const infoPanelTextArea = document.getElementById('infoPanelCopy');
    infoPanelTextArea.value = '';
    infoPanelTextArea.style.height = '1px';
    if(infoPanel.style.opacity <= 0) {
        infoPanel.style.display = 'none';
        clearTimeout(timeout);
    }

}
function hideInfoPanel(id, handler) {
    const infoPanel = document.getElementById('infoPanel');
    timeout = setTimeout(hideInfoPanelTransitionEnd, 1000);
    infoPanel.style.opacity = 0;
}
function setBorderStyle(element, value) {
    if(value) {
        if(!element.parentNode.isSubElement) {
            element.style.left = (element.offsetLeft - 2) + 'px';
        }
        element.style.top = (element.offsetTop - 2) + 'px';
        element.style.borderWidth = '3px';
        element.style.borderColor = '#6cad38';
    } else {
        if(!element.parentNode.isSubElement) {
            element.style.left = (element.offsetLeft + 2) + 'px';
        }
        element.style.top = (element.offsetTop + 2) + 'px';
        element.style.borderWidth = '1px';
        element.style.borderColor = 'black';
    }
}
function showHideArticleInfoPanel(id, handler, value) {
    const textContentElement = handler.parentNode;
    const textContentContainerElement = handler.parentNode.parentNode;
    if(value && !textContentElement.borderShown) {
        textContentElement.borderShown = true;
        if(textContentContainerElement.isSubElement) {
            setBorderStyle(textContentContainerElement.content.mainElement.content.textField, true);
        } else if (textContentContainerElement.isMainElement) {
            setBorderStyle(textContentContainerElement.content.subElement.content.textField, true);
        }
        setBorderStyle(textContentElement, true);
        showInfoPanel(id, textContentElement);
    } else if (textContentElement.borderShown) {
        textContentElement.borderShown = false;
        if(textContentContainerElement.isSubElement) {
            setBorderStyle(textContentContainerElement.content.mainElement.content.textField, false);
        } else if (textContentContainerElement.isMainElement) {
            setBorderStyle(textContentContainerElement.content.subElement.content.textField, false);
        }
        setBorderStyle(textContentElement, false);
        hideInfoPanel(id, textContentElement);
    }


    // console.log('showHideArticleInfoPanel -> ', id, handler, value);
}
function setAdVisibility() {

}
function getAdBackground(element) {
    return element.getElementsByClassName('ad-background')[0];
}
function checkAdValue(item, htmlElement, baseElement) {
    let canPlaceAd = false;
    const id = getId(htmlElement);
    const adClass = 'ad'+item.adClass;
    // const backgroundElement = getAdBackground(baseElement);

    if(item.adValue.substr(0,1) == '2') {
        const neighbourElement = isEven(id) ? document.getElementById('theme_' + (id + 1)) :  document.getElementById('theme_' + (id - 1));
        const neighbourAd = isEven(id) ? document.getElementById('ad-content_' + (id + 1)) :  document.getElementById('ad-content_' + (id - 1));

        if(!neighbourElement.hasAd) {
            getAdBackground(baseElement).classList.add(adClass); // just do it if using an adcontainer
            htmlElement.hasAd = true;

            baseElement.style.display = 'block';
            neighbourElement.hasAd = true;
            // set visibility of neigbourclip
            // baseElement.getElementsByClassName('ad-close')[0].style.display = isEven(getId(baseElement)) ? 'block' : 'none';
            const width = (2 * baseElement.offsetWidth) + 'px';
            if (isEven(getId(baseElement))) {
                htmlElement.inAdPages = [htmlElement, neighbourElement];
                // console.log('baseElement ist gerade', baseElement);
                baseElement.getElementsByClassName('ad-close')[0].style.display = 'block';
                baseElement.style.width = width;
                neighbourAd.style.display = 'none';
            } else {
                neighbourElement.inAdPages = [neighbourElement, htmlElement];
                // console.log('baseElement ist ungerade', baseElement);
                baseElement.getElementsByClassName('ad-close')[0].style.display = 'none';
                neighbourAd.style.width = width;
                baseElement.style.display = 'none';
                neighbourAd.style.display = 'block';
            }
            htmlElement.adClass = adClass;
            neighbourElement.adClass = adClass;
            // baseElement.style.display = isEven(getId(baseElement)) ? 'block' : 'none';

            getAdBackground(baseElement).style.borderLeft = isEven(getId(baseElement)) ? adBorderStyle : 'none';

            htmlElement.hasNeighbour = true;
            htmlElement.neighbour = neighbourAd;
            getAdBackground(neighbourAd).classList.add(adClass);
            neighbourElement.hasNeighbour = true;
            neighbourElement.neighbour = baseElement;
            // neighbourAd.style.display = 'block';
            neighbourAd.getElementsByClassName('ad-close')[0].style.display = isEven(getId(neighbourElement)) ? 'block' : 'none';
            getAdBackground(neighbourAd).style.borderLeft = isEven(getId(neighbourElement)) ? adBorderStyle : 'none';
            // console.log('checkAdValue DoublePage -> left page', item.adValue, htmlElement, neighbourElement);
            canPlaceAd = true;
            calcPagePercentage();
        }
    } else {
        // console.log('checkAdValue SinglePage-> ', item.adValue, htmlElement, getId(htmlElement));
        getAdBackground(baseElement).classList.add(adClass); // just do it if using an adcontainer
        htmlElement.hasAd = true;
        htmlElement.adClass = adClass;
        htmlElement.inAdPages = [];
        htmlElement.inAdPages.push(htmlElement);
        baseElement.style.display = 'block';
        canPlaceAd = true;
        calcPagePercentage();
    }
    return canPlaceAd;
}
function checkDroppedItems(item, dropElement) {
    pageHitTest = false;
    const parentElement = dropElement;
    const checkValue = (item.scope === 'ad') ? parentElement.hasAd : parentElement.hasArticle;
    if (!checkValue) {
        // console.log(item, 'hasHitTest', 'Theme is now visible: ', parentElement.hasArticle, parentElement, 'ScopeOfItem: ', item.scope, item.adValue, item.adClass);
        const targetElement = item.scope === 'ad' ? dropElement.getElementsByClassName('ad-content')[0] : dropElement.getElementsByClassName('theme-content')[0];
        if(item.scope === 'ad') {
            pageHitTest = checkAdValue(item, parentElement, targetElement);
            // console.log('adClass -> ', item.adClass);
        } else {
            setArticleStatus(parentElement,null);
            parentElement.hasArticle = true;
            parentElement.articleId = getId(parentElement);
            parentElement.inArticlePages.push(parentElement);
            parentElement.content.textField.addEventListener("mousedown", initTextFieldMouseEvent , false);
            targetElement.style.display = 'block';
            pageHitTest = true;
            calcPagePercentage();
        }
    } else if (checkValue) {
        pageHitTest = false;
    }
}
function setArticleStatus(element, value) {

    const status = value == null ? 'planned' : value;
    element.articleStatus = status;
    if (element.inArticlePages.length > 0) {
        const array = element.inArticlePages;
        for (let i in array) {
            const elementInArray = array[i];
            // console.log('inArray? -> ', {elementInArray});
            elementInArray.articleStatus = status;
        }
    }
}
function setIconVisibility(dragElement, active){
    if(!dragElement) {
        return;
    }
    const passiveIcon = dragElement.getElementsByClassName('passive')[0];
    const activeIcon = dragElement.getElementsByClassName('active')[0];
    passiveIcon.style.display = active ? 'none' : 'block';
    activeIcon.style.display = active ? 'block' : 'none';
}
function clearDragItem() {
    this.dragItem = null;
    // console.log('clearDragItem');
    clearTimeout(timeout);
}
function dragItemOnTransitionEnd(event) {
    // console.log('Transition ended close', event.target.id, event.propertyName);
    setIconVisibility(this.dragItem, 0);
    setVisibility(event.target.id, 1);
    this.dragItem.removeEventListener('transitionend',dragItemOnTransitionEnd, false);

    timeout = setTimeout(clearDragItem, 500);
}
function dragItemDragstart(event) {
    // console.log('dragstart aus initDragArticleItems', this.dragItem);
    event.target.dragged = true;
    this.dragItem.style.left = this.dragItem.origX + 'px';
    this.dragItem.style.top =  this.dragItem.origY + 'px'; // - 100
    setIconVisibility(this.dragItem, 1);
    event.target.classList.add("dragging");
    this.dragItem.removeEventListener('dragstart', dragItemDragstart, false);
}
function dragItemDragend(event) {
    if(event.target.dragged) {
        event.target.dragged = false;
        //checkDroppedItems(event.target, event);
        if(!pageHitTest) {
            // console.log('no hit detected');
            this.dragItem.style.transition = 'all 0.2s ease-in-out';
            this.dragItem.style.left = this.dragItem.origX + 'px';
            this.dragItem.style.top = this.dragItem.origY + 'px';
        } else {
            // console.log('hit detected');
            this.dragItem.style.transition = 'all 0s ease-in-out';
            this.dragItem.style.left = this.dragItem.origX + 'px';
            this.dragItem.style.top = this.dragItem.origY + 'px';
            pageHitTest = false;
            setIconVisibility(this.dragItem, 0);
            timeout = setTimeout(clearDragItem, 100);
        }
    }

    event.target.classList.remove("dragging");
    this.dragItem.removeEventListener('dragend', dragItemDragend, false);

}
function initDragArticleItems(item){
    item.addEventListener('dragstart', (event) => dragItemDragstart(event));
    item.addEventListener('dragend', (event) => dragItemDragend(event));
    item.addEventListener('transitionend', (event) => dragItemOnTransitionEnd(event));
}

function addArticle(name) {
    // console.log('addArticle - mouseOver -> ', name);
    const dragName = 'dragItem_' + name;
    initDragElements(dragName,'article');
}
function isPAGEElement(element) {
    let isPage = false;
    if(element.id) {
        isPage = element.id.substr(0,4) === 'page' ? true : false;
    }
    return isPage;
}

function checkForDraggablePages() {
    let clips;
    clips = document.getElementsByClassName('page');
    for(let i in clips) {
        const element = clips[i];
        if(isPAGEElement(element)) {
            const container = element.parentNode.parentNode;
            const rootContainer = container.parentNode;
            const elementX = Math.floor(getElementPosition(element).x);
            const elementY = Math.floor(getElementPosition(element).y);
            const elementWidth = element.childNodes[0].offsetWidth;
            const elementHeight = element.childNodes[0].offsetHeight;
            const newElementX = Math.floor(getOffset(element).left);
            const newElementY = Math.floor(getOffset(element).top);
            pageArray.push([element, {x: elementX, y: elementY, width: elementWidth, height:elementHeight, newY:newElementY, newX:newElementX}]);
        }
    }
    convertTouchEvents();
}

function editArticle() {
    // console.log('editarticle');
}
function editText() {
    // console.log('editText');
}
function init() {
    const clip = document.getElementsByClassName('listItem');
    clip.onmouseup = function() {
        changeMagazine();
    }
}
function checkFormDataForTestIssues(form) {
    let data = new FormData(form);
    let dateObj = {};
    for (const [key, value] of data) {
        dateObj[key] = value;
    }
    return data;
}
function checkFormDataForTestIssuesObj(form) {
    // console.log('form -> ', form);
    const data = new FormData(form);
    // console.log('formdata in Obj -> ', data);
    let dateObj = {};
    for (const [key, value] of data) {
        dateObj[key] = value;
        // console.log('formdata in Obj -> ', key,value);
    }
    return dateObj;
}
function setMd5Password(pw, event, form) {
    const request = new XMLHttpRequest();
    if(form === 'addUser' || form === 'editUser') {
        request.form = form;
        request.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                const md5Password = JSON.parse(this.responseText); // JSON.parse(this.responseText);
                addUserData(event, md5Password, this.form);
            }
        };
        request.open("POST", 'inc/setPw.php');
        request.setRequestHeader( "Content-Type", "application/json; charset=UTF-8");
        request.send(pw);
    }
}
function capitalize(string)
{
    return string[0].toUpperCase() + string.slice(1);
}
function addUserData(event, md5Password, form=null) {
    let data = new FormData(event.target.form);
    let dateObj = {};
    let permissionsString = '{';
    let queryArray = [];
    const isValidForm = validateData(data, form);

    for (const [key, value] of data) {
        dateObj[key] = value;
    }

    for(let i = 1; i<10; i++) {
        const permission = 'userPermission_' + i;
        if(dateObj[permission]){
            permissionsString += permission + ':' + dateObj[permission] + ',';
        }
    }
    permissionsString = permissionsString.substring(0, permissionsString.length - 1) + '}';
    const userNameString = 'userName' + capitalize(form);
    const emailString = 'email' + capitalize(form);
    let query;
    if(form === 'addUser') {
        query = createAddUserQuery(dateObj[userNameString], dateObj[emailString], md5Password, permissionsString);
    } else if(form === 'editUser') {
        query = createEditUserQuery(dateObj[userNameString], dateObj[emailString], md5Password, permissionsString);
    }

    queryArray.push(query);
    // console.log('addUserData -> ', event, md5Password, form, queryArray, dateObj);

    const request = new XMLHttpRequest();
    request.form = form;
    request.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            const response = JSON.parse(this.responseText);
            // console.log(response.error);
            if(response.error != 0) {
                const error = [];
                if(response.error.substring(0,9) == 'Duplicate') {
                    error.push(['sqlError', 'Der User existiert schon.']);
                    showError(error);
                }

            } else {
                debug([this.status, this.readyState, response]);
                showSuccess(this.form);
            }
        } else {
            debug([this.status, this.readyState]);
        }
    };

    if(isValidForm) {
        request.open("POST", 'controller/addUser.php');
        request.setRequestHeader( "Content-Type", "application/json; charset=UTF-8");
        request.send(JSON.stringify(queryArray));
    }
}

function selectLoginValue(value){
    // TODO: can be deleted -> check 'login.php' and delete function too
}
function showSuccess(form) {
    // openClose('','','','')
    cancel('editDialog',form);
    if(form == 'adduser') {
        alert("User erfolgreich hinzugefügt");
    } else if(form == 'edituser') {
        alert("User erfolgreich geändert");
    }
}
function showError (error) {
    let errorText = "";
    for(let i in error){
        const errorType = error[i][0];
        switch(errorType) {
            case 'sqlError':
                errorText += "SQL-Fehler:\n";
                break;
            case 'userName':
                errorText += "Bitte füllen Sie folgende Felder aus:\n";
                break;
            case 'email':
                errorText += "Bitte füllen Sie folgende Felder aus:\n";
                break;
            case 'password':
                errorText += "Bitte füllen Sie folgende Felder aus:\n";
                break;
            default:
                errorText += errorType;
        }

        if(error[i][1] == 'empty') {
            errorText += " ist leer\n";
        } else {
            errorText += error[i][1] + "\n";
        }
    }
    alert(errorText);
}
function validateData(data, form) {
    let result = false;
    const error = [];
    if(form == 'addUser' || form == 'editUser') {
        for (const [key, value] of data) {
            if(key == 'userName'+capitalize(form) && value == '') {
                error.push(['Username', 'empty']);
            }
            if(key == 'email'+capitalize(form) && value == '') {
                error.push(['E-Mail', 'empty']);
            }
            if(key == 'password'+capitalize(form) && value == '' && form == 'addUser') {
                error.push(['Passwort', 'empty']);
            }
        }
        if(error.length > 0) {
            showError(error)
        } else {
            result = true;
        }
    }

    return result;
    // sendData(result);
}

function save(value, form) {
    // alert("save -> " + value + form);
    // console.log("save -> ", event);

    const userForm = form === 'addUser' || form === 'editUser';
    if (userForm) {
        event.preventDefault();
        const pw = event.target.form['password' + capitalize(form)].value;
        // console.log(event.target.form['password' + capitalize(form)]);

        if(!!pw.trim() && userForm) {
            // console.log('password = ', pw);
            setMd5Password(pw, event, form);
        } else if(form === 'editUser'){
            // console.log('no password');
            addUserData(event, null, form);
        }
    }
    if(form === 'deleteIssue') {

        const answer = window.confirm("Sind Sie sicher, dass Sie diese Ausgabe löschen möchten?\nDiese Aktion kann nicht rückgängig gemacht werden.");
        if (answer) {
            //some code
        } else {
            event.preventDefault();
            //some code
        }
    }
}
function getUserGroupValue(fieldId) {
    let value = null;
    if(getElement(fieldId)) {
       value = getElement(fieldId).value.split(',');
    }
    return value;
}
function setArticleSubject(articleTitle, articleStatusFrom = null, articleStatusTo, warnings={}) {
    let value = '';
    if(articleStatusFrom) {
        value = 'Artikel "' + articleTitle + '" wurde von "' + articleStatusFrom + '" auf ' + '"' + articleStatusTo + '" geändert.';
    }else {
        value = 'Artikel "' + articleTitle + '" wurde auf ' + '"' + articleStatusTo + '" gesetzt.';
    }
    return value;
}
function setArticleBody(magazine, issue, magazineId, issueId, articleTitle, articleStatusFrom = null, articleStatusTo, pages, warnings={}) {
    let value = '';
    const actualYear = getSetIssueYear().year;
    const userEmail = document.getElementById('mainUserEmail').value;
    const userName = document.getElementById('mainUserName').value;
    // console.log('setArticleBody -> ', userEmail, userName);
    const name = '<a href="mailto:'+ userEmail +'">' + userName + '</a>';
    let fromToStatusText;
    if(articleStatusFrom) {
        fromToStatusText = warnings.direction === 'down' ? '<strong style="color: #FF0000;">wurde von "' + articleStatusFrom + '" auf ' + '"' + articleStatusTo + '"!</strong>' : ' wurde von "' + articleStatusFrom + '" auf ' + '"' + articleStatusTo + '"';
        value = 'Magazin: ' + magazine + ',<br>Ausgabe: ' + issue + ',<br>Jahr: ' + actualYear + ',<br>Artikel "' + articleTitle + '",<br>' + fromToStatusText + pages + ' geändert.<br>('+ name +')';
    }else {
        value = 'Magazin: ' + magazine + ',<br>Ausgabe: ' + issue + ',<br>Jahr: ' + actualYear + ',<br>Artikel "' + articleTitle + '",<br>wurde auf ' + '"' + articleStatusTo + '"' + pages + ' gesetzt.<br>('+ name +')';
    }
    const article = 'Magazin: ' + magazine + '/' + issue;
    return value + '<br><br>direkt zur Ausgabe <a href="https://www.tourenfahrer.de/planify/index.php?magazines=' + magazineId + '&issue=' + issueId + '&year=' + actualYear + '">' + article + '</a>';
}
function sendNotificationMail(notificationMails) {
    const request = new XMLHttpRequest();
    request.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            const str = 'onreadystatechange ->  ' + this;
            const additionalString = str + this.responseText;
            // alert('Benachrichtigungen Verschickt.');
            window.location.reload();
            // alert('Benachrichtigungen Verschickt.');
            // osAjaxBackend.BindAfterAjaxRequest(function(){alert('AJAX refresh')});
            debug([this.responseText]);
        }
    };
    request.open("POST", 'controller/sendNotificationMail.php');
    request.setRequestHeader( "Content-Type", "application/json; charset=UTF-8");
    request.send(JSON.stringify(notificationMails));
}
function addUserGroup(userGroup=[], article = {}, magazine={}, warnings={}){
    let newUserGroup = {};
    newUserGroup.mailTo = userGroup[0] ? userGroup[0].mail.join(',') : null;
    newUserGroup.nameTo = userGroup[0] ? userGroup[0].name.join(',') : null;
    if (userGroup.length > 1){
        for(let i = 1; i < userGroup.length; i++) {
            if(userGroup[i]) {
                const mailValue = ',' + userGroup[i].mail.join(',');
                const nameValue = ',' + userGroup[i].name.join(',');
                newUserGroup.mailTo += mailValue;
                newUserGroup.nameTo += nameValue;
            }
        }
    }
    newUserGroup.subject = setArticleSubject(article.title,article.status.from.translation, article.status.to.translation, warnings);
    newUserGroup.body = setArticleBody(magazine.title, magazine.issue, magazine.magazineId, magazine.issueId, article.title, article.status.from.translation, article.status.to.translation, magazine.pages, warnings);
    return newUserGroup;
}
function notifyTeamMembers(magazine, issue, magazineId, array){
    const issueId = issue;
    const viewerGroup = {
        mail: getUserGroupValue('notificationTo_1'),
        name: getUserGroupValue('notificationNameTo_1')
    };
    const onlineGroup = {
        mail: getUserGroupValue('notificationTo_2'),
        name: getUserGroupValue('notificationNameTo_2')
    };
    const graphicGroup = {
        mail: getUserGroupValue('notificationTo_3'),
        name: getUserGroupValue('notificationNameTo_3')
    };
    const editorGroup = {
        mail: getUserGroupValue('notificationTo_4'),
        name: getUserGroupValue('notificationNameTo_4')
    };
    const editorInChiefGroup = {
        mail: getUserGroupValue('notificationTo_5'),
        name: getUserGroupValue('notificationNameTo_5')
    };
    const adminGroup = {
        mail: getUserGroupValue('notificationTo_6'),
        name: getUserGroupValue('notificationNameTo_6')
    };
    let mailToArray = [];
    for(let i in array){
        const subArray = array[i];
        if(!subArray[1]) {
            alert('no title in page ' + subArray[0][0]);
            break;
        }
        let pages;

        // console.log('pages -> ', subArray[0]);
        if(subArray[0].length === 1){
            pages = ' auf der Seite ' + subArray[0][0];
        } else {
            const firstPage = subArray[0][0];
            const lastPage = subArray[0][(subArray[0].length-1)];
            pages = ' auf den Seiten ' + firstPage + ' - ' + lastPage;
        }

        const status = subArray[2];
        const articleStatusFrom = status.from.name;
        const articleStatusTo = status.to.name;
        const magazineObj = {
            pages: pages,
            magazine: magazine,
            magazineId: magazineId,
            title: magazine,
            issue: issue,
            issueId: issue,
        }
        const article = {
            id: subArray[0][0],
            title: subArray[1],
            status: {
                from: {
                    name: status.from.name,
                    id: status.from.id,
                    translation: status.from.translation
                },
                to: {
                    name: status.to.name,
                    id: status.to.id,
                    translation: status.to.translation
                }
            }
        };
        let mail = {};
        let warnings = {
            direction: 'up',
            text: 'attention'
        }
        if(articleStatusTo === 'planned') {
            if (articleStatusFrom === 'layout-final') {
                warnings.direction = 'down';
                mail = addUserGroup([graphicGroup, onlineGroup, adminGroup],  article, magazineObj, warnings);
            }
        } else if(articleStatusTo === 'data-arrived') {
            if (articleStatusFrom === 'layout-final') {
                warnings.direction = 'down';
                mail = addUserGroup([graphicGroup, onlineGroup, adminGroup],  article, magazineObj, warnings);
            }
        } else if(articleStatusTo === 'layout-ready') {
            if (articleStatusFrom === 'data-arrived') {
                mail = addUserGroup([graphicGroup],  article, magazineObj, warnings);
            } else if(articleStatusFrom === 'layout-arrived') {
                mail = addUserGroup([graphicGroup, editorInChiefGroup],  article, magazineObj, warnings);
            } else if(articleStatusFrom === 'layout-final') {
                warnings.direction = 'down';
                mail = addUserGroup([graphicGroup, onlineGroup, adminGroup],  article, magazineObj, warnings);
            }
        } else if(articleStatusTo === 'layout-in-progress') {
            if (articleStatusFrom === 'layout-final') {
                warnings.direction = 'down';
                mail = addUserGroup([graphicGroup, onlineGroup, adminGroup],  article, magazineObj, warnings);
            }
        } else if(articleStatusTo === 'layout-arrived') {
            if (articleStatusFrom === 'layout-in-progress') {
                mail = addUserGroup([editorInChiefGroup],  article, magazineObj, warnings);
            } else if (articleStatusFrom === 'layout-review') {
                mail = addUserGroup([editorGroup, editorInChiefGroup],  article, magazineObj, warnings);
            } else if (articleStatusFrom === 'layout-final') {
                warnings.direction = 'down';
                mail = addUserGroup([graphicGroup, onlineGroup, adminGroup],  article, magazineObj, warnings);
            }
        } else if(articleStatusTo === 'layout-review') {
            if (articleStatusFrom === 'layout-final') {
                warnings.direction = 'down';
                mail = addUserGroup([graphicGroup, onlineGroup, adminGroup],  article, magazineObj, warnings);
            }
        } else if(articleStatusTo === 'layout-final') {
            if (articleStatusFrom === 'layout-review') {
                mail = addUserGroup([graphicGroup, onlineGroup, adminGroup],  article, magazineObj, warnings);
            }
        }
        if (mail.mailTo) {
            mailToArray.push(mail);
        } else {
            window.location.reload();
            break;
        }
    }
    // console.log(mailToArray);

    if(mailToArray.length > 0) {
        // console.log(mailToArray);
        sendNotificationMail(mailToArray);
    } else {
        // console.log('is Empty -> ',mailToArray);
        mailToArray = {name:'reload'};
        sendNotificationMail(mailToArray);
        // alert('empty request');
    }
}
function addValuesToNotificationForm(magazine, issue) {
    // const form = getElement('notification');
    let articleId = 0;
    let articleTitle;
    let articleStatus;
    let articleStatusId;
    let articleStatusTranslated;
    let articleSourceStatus;
    let articleSourceStatusId;
    let articleSourceStatusTranslated;
    const pageArray = [];
    let arrayPos = -1;
    for (let i in themesArray) {
        const item = themesArray[i];
        if (item.hasArticle) {
            if (articleId != item.articleId && item.articleStatus != item.articleSourceStatus) {
                // console.log('is new -> ', item);
                arrayPos += 1;
                articleId = item.articleId;
                articleTitle = getTitle(item);
                articleStatus = item.articleStatus;
                articleStatusId = articleStatusIds.indexOf(articleStatus);
                articleStatusTranslated = articleStatusTranslations[articleStatusId];
                articleSourceStatus = item.articleSourceStatus;
                articleSourceStatusId = articleStatusIds.indexOf(articleSourceStatus);
                articleSourceStatusTranslated = articleStatusTranslations[articleSourceStatusId];
                pageArray.push([[articleId], articleTitle, {from: {name: articleSourceStatus, id: articleSourceStatusId, translation: articleSourceStatusTranslated}, to: {name: articleStatus, id: articleStatusId, translation: articleStatusTranslated}}]);
            } else if (articleId == item.articleId) {
                pageArray[arrayPos][0].push(getId(item));
            }
            // console.log(item);
        }
        if (item.hasAd) {
            // console.log('hasAd -> ', item);
        }

    }

    if(pageArray.length > 0) {
        // console.log('pageArray -> ', pageArray);
        notifyTeamMembers(magazineArray[magazine], issue, magazine, pageArray);
    } else {
        let mailToArray = {name:'reload'};
        sendNotificationMail(mailToArray);
    }

}
function showNotificationPanel () {
    const form = getElement('notificationContainer');
    form.classList.add('visible');
    form.targetForm = form;
    const setInVisible = function() {
        getElement('notificationContainer').classList.remove('visible')
        getElement('notificationContainer').removeEventListener('click',setInVisible, false);
    }
    form.addEventListener('click', (event) => setInVisible());
}
function notify(magazine, issue) {
    // addValuesToNotificationForm(magazine, issue);
    // TODO: just for testing issues
    // showNotificationPanel();
}
function uploadScreenshot(event, form) {
    // TODO create message function
    event.preventDefault();
    // console.log('uploadScreenshot -> ', event, form);
}
function contactSupportTeam(magazines, issue) {
    // TODO create message function
    // console.log('contactSupportTeam -> ', magazines,issue);
}
function hideLayout() {
    const layer = document.getElementById('overLayer');
    const pdfReader = document.getElementById('pdfReader');
    const pdfReaderAltTag = document.getElementById('pdfReaderAltTag');
    if (pdfReader.tagName === 'OBJECT') {
        pdfReaderAltTag.href = '';
        pdfReaderAltTag.innerHtml = '';
        pdfReader.data = '';
    } else if (pdfReader.tagName === 'EMBED' || pdfReader.tagName === 'IFRAME'){
        pdfReader.src = '';
    }

    layer.style.display = 'none';
}
function showLayout(event, id,pageObj= {}){
    // console.log('showLayout-> ', event , id, {pageObj});
    let fileAttributes;
    const actualPage = Number(Number(pageObj.pageId)+1 - Number(pageObj.articleId));
    if(isMobile) {
        if(pageObj.file.length > 10) {
            const pdfFormLink = document.getElementById('pdfLink');
            pdfFormLink.value = 'https://www.tourenfahrer.de/planify/' + pageObj.file;
            const pdfFormData = document.getElementById('pdfForm');
            pdfFormData.submit();
        }else {
            alert('Bitte das Layout erneut hochladen.');
        }

    } else {

        const layer = document.getElementById('overLayer');
        const layerCloseBtn = document.getElementById('overLayerCloseBtn');
        const pdfReader = document.getElementById('pdfReader');
        const pdfReaderAltTag = document.getElementById('pdfReaderAltTag');

        layerCloseBtn.onclick = function () {
            hideLayout();
        }
        layerCloseBtn.ontouchstart = function () {
            hideLayout();
        }
        fileAttributes = '#page=' + actualPage + '&view=Fit&toolbar=1&scrollbar=1&navpanes=1&collab=DB&zoom=page-fit&background=#ffffff';
        const pdfFile = pageObj.file + fileAttributes;
        if(pageObj.file.length > 10) {
            if (pdfReader.tagName === 'OBJECT') {
                pdfReaderAltTag.href = pageObj.file;
                pdfReaderAltTag.innerHtml = String(pageObj.file);
                pdfReader.data = pdfFile;
            } else if (pdfReader.tagName === 'EMBED' || pdfReader.tagName === 'IFRAME'){
                pdfReader.src = pdfFile;
            }
            layer.style.display = 'block';
        } else {
            alert('Bitte das Layout erneut hochladen.');
        }
    }




}

function downloadLayouts(magazineId, issueId, year) {
    // console.log('Jahr = ', getSetIssueYear().year)
    const downloadRequest = new XMLHttpRequest();
    const downloadYear = getSetIssueYear().year;
    downloadRequest.target = this;
    const data = new FormData();
    data.append('magazineId', magazineId);
    data.append('issueId', issueId);
    data.append('year', downloadYear);
    downloadRequest.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const str = 'onreadystatechange ->  ' + this;
            debug([str, this.responseText]);
            const result = JSON.parse(this.responseText);
            // this.target.onReady();
            if(result.status === 'error'){
                alert(result.data);
            } else if(result.status === 'success') {
                const downloadFrame = document.getElementById('downloadFrame');
                downloadFrame.src = result.data;
            }
        }
    };
    downloadRequest.open("POST", 'controller/downloadLayouts.php', true);
    // console.log('sending -> ', data);
    downloadRequest.send(data);
}
function showHideUploadIcon(id, item, value) {

    const containerElement = item.parentNode.parentNode;
    const uploadButton = containerElement.content.uploadButton;
    if(!containerElement.isSubElement && uploadButton) {
        uploadButton.style.display = (value) ? 'block' : 'none';
    }
    showHideArticleInfoPanel(id, item, value);
}
function articleMouseDown(id, item) {
    item.warning = false;
    // console.log('articleMouseDown -> ', id, item);
}
function printDialog(table, scope) {
    const divToPrint = document.getElementById(table);
    // divToPrint.getElementsByClassName('print-table')[0].classList.add('maxSize');
    let newWin = window.open("");
    newWin.document.write(divToPrint.innerHTML);
    newWin.print();
    newWin.close();
}
function printAd(magazines, issue) {
    // console.log('printAds -> ', magazines,issue);
    const actualYear = getSetIssueYear().year;
    const magazineTitle = magazineArray[magazines];
    const dataObj = {magazines: magazines, issue: issue, year: actualYear, title: magazineTitle};
    const request = new XMLHttpRequest();
    request.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            const str = 'onreadystatechange ->  ' + this;
            // debug([str, this.responseText]);
            const printContainer = document.getElementById('printDialog');
            const printContent = document.getElementById('printContent');
            const availablePrintContents = printContainer.getElementsByClassName('print-content');
            if(availablePrintContents.length > 0) {
                availablePrintContents[0].remove();
            }
            // console.log('prints ->> ',availablePrintContents.length);
            printContent.insertAdjacentHTML("afterend",this.responseText);
            openClose('printDialog', 'open', 'printAd', 'Drucken')
            // printContainer.style.display='block';
        }
    };
    const phpForm = 'controller/getAdsForPrint.php';
    request.open("POST", phpForm);
    request.setRequestHeader( "Content-Type", "application/json; charset=UTF-8");
    request.send(JSON.stringify(dataObj));
}

function sendData(data) {
    alert('old -> ' + data);
}
function setVisibility(clip, value) {
    const targetClip = document.getElementById(clip);
    if(targetClip) {
        targetClip.style.display = value == 1 ? 'block' : 'none';
        targetClip.onTransitionEnd = function() {
            // console.log('onTransitionEnd overwritten');
        }
    }

}
function changeTransitionDuration(clip, value) {
    const targetClip = document.getElementById(clip);
    if(targetClip) {
        targetClip.style.transition = 'all ' + value + 's ease-in-out';
    }
}
function cancel(value, form) {
    // console.log('cancel -> ', form);
    const isForm = form === 'addUser' || form === 'editUser';
    if(isForm && event) {
        event.preventDefault();
    }
    const targetClip = document.getElementById(value);
    targetClip.style.transition = 'all .2s ease-in-out';
    openDialogElement = targetClip;
    let timeout;
    timeout = setTimeout(closeDialog,10);
};
function closeDialog() {
    // console.log('minimize -> ', openDialogElement);
    openDialogElement.style.transform = 'scale(0.1)';
    openDialogElement.style.opacity = '0';
    openDialogElement.tl = this;
    onTransitionEnd = function(event) {
        // console.log('Dialog Transition ended close', event.propertyName);
        setVisibility('editDialog', 0);
        const targetFormId = visibleForm.id;
        setVisibility(targetFormId, 0);
        onTransitionEnd = function(event) {
            // console.log('do nothing', event.propertyName);
        }
    };
}

function openDialog() {
    openDialogElement.tl = this;
    onTransitionEnd = function(event) {
        // console.log('Dialog Transition ended open', event.propertyName);
        changeTransitionDuration(openDialogElement.id, 0);
        onTransitionEnd = function(event) {
            // console.log('do nothing', event.propertyName);
        }
    };
    openDialogElement.style.opacity = '1';
    openDialogElement.style.transform = 'inherit';
    // console.log('openDialogElement', openDialogElement)
}
let uploadInterval;
let uploadRequest;
function setProgressBarStatus(percentage, txt){
    document.getElementById('progressDivId').style.display = 'block';
    document.getElementById('progressBar').style.width = percentage + '%';
    document.getElementById('percentTxt').innerHtml = percentage + '%';
    const textElement = document.getElementById('percentTxt');

    if(percentage === 100) {
        textElement.value = 'Bilder werden generiert' + txt;
    } else {
        textElement.value = percentage + '%';
    }
}
let progressDots = '';
function watchUploadProgress() {
    const  percentage = Math.round((uploadRequest.loaded/uploadRequest.total)*100);
    if(progressDots != '...') {
        progressDots += '.';
    } else {
        progressDots = '';
    }
    setProgressBarStatus(percentage,progressDots);
    // console.log('watchUploadProgress -> ', "percent " + percentage + '%');
}
function transferUploadProgress(event){
    uploadRequest.loaded = event.loaded;
    uploadRequest.total = event.total;
    const percentage = Math.round((event.loaded/event.total)*100);
    setProgressBarStatus(percentage);
    // console.log("transferUploadProgress -> ", event.loaded, event.total, "percent " + percentage + '%');
}
function transferUploadComplete(event){
    clearTimeout(uploadInterval);
    // console.log("The transfer is complete.");
}
function transferUploadFailed(event){
    //console.log("An error occurred while transferring the file.");
}
function transferUploadCanceled(event){
    // console.log("The transfer has been canceled by the user.");
}

function defineForm(form, button, onReady) {

    const btn = button + 'Dialog_cancel';
    const targetCloseButton = document.getElementById(btn);

    // console.log('defineForm -> ', form, button, targetCloseButton );
    if(targetCloseButton) {
        targetCloseButton.addEventListener("click", function(event){
            event.preventDefault()
        });
    }


    const targetForm = document.getElementById(form);
    targetForm.phpIdentifier = button;
    targetForm.formIdentifier = form;
    targetForm.onReady = onReady ? onReady : '';

    // TODO delete this for add issue
    if(button != 'addIssue' && button != 'deleteIssue') {
        targetForm.addEventListener('submit', (event) => {
            // console.log('submit -> ', event.target.id, event.target.magazineId, event.target.issueId);
            if(event.target.id === 'uploadImageDialogForm'){
                saveArticle(event.target.magazineId, event.target.issueId, false);
            }
            event.preventDefault();
            new FormData(targetForm);
        });
        targetForm.addEventListener('formdata', (event) => {
            let data = event.formData;
            const actualYear = getSetIssueYear().year;
            data.append('actualYear', actualYear);

            if(event.target.phpIdentifier === 'uploadImage') {

                const fileSelect = document.getElementById('uploadImageDialogFormUploadFile');
                const file = fileSelect.files[0];

                if(file) {
                    // fileObj = {file: file,file.name}
                    data.append('photos[]', file,file.name);
                } else {
                    data = null;
                    alert('Keine Datei Ausgewählt.');
                }
            } else if (event.target.phpIdentifier === 'contactSupportTeam') {
                const fileSelect = document.getElementById('contactSupportTeamDialogFormUploadFile');
                const file = fileSelect.files[0];
                if(file) {
                    // fileObj = {file: file,file.name}
                    data.append('photos[]', file,file.name);
                } else {
                    // data = null;
                    // alert('Keine Datei Ausgewählt.');
                }
            }
            let dataObj = {};
            for (const [key, value] of data) {
                dataObj[key] = value;
            }
            // magazineArray[magazine]

            // console.log({dataObj});
            const magazineToString = magazineArray[dataObj.magazines];
            dataObj.magazines = magazineToString;
            const request = new XMLHttpRequest();
            request.target = event.target;
            // location.reload();
            if(event.target.phpIdentifier === 'uploadImage') {
                request.onprogress = function (event){
                    const percentage = Math.round((event.loaded/event.total)*100);
                    uploadRequest.percentage = percentage;
                    uploadRequest.loaded = event.loaded;
                    uploadRequest.total = event.total;
                    // console.log('onprogress percent ' + percentage + '%', event, this);
                }
                uploadRequest = request;
                uploadRequest.loaded = 0;
                uploadRequest.total = 100;
                uploadInterval = setInterval(watchUploadProgress,500);
                request.upload.addEventListener("progress", transferUploadProgress, false);
                request.addEventListener("load", transferUploadComplete, false);
                request.addEventListener("error", transferUploadFailed, false);
                request.addEventListener("abort", transferUploadCanceled, false);
                request.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        const str = 'onreadystatechange ->  ' + this;
                        debug([str, this.responseText]);
                        const result = JSON.parse(this.responseText);
                        this.target.onReady();
                        if(result.data === 'allFilesWritten'){
                            location.reload();
                        } else if(result.data === 'pageAmountToHigh') {
                            alert('Das Layout hat zuviele Seiten.');
                        } else if(result.data === 'pageAmountToSmall') {
                            alert('Das Layout hat zuwenige Seiten.');
                        } else if(result.data === 'fileTypeNoPdf') {
                            alert('Die Datei ist kein PDF.')
                        } else if(result.data === 'fileAlreadyExist') {
                            alert('Die Datei existiert schon.')
                        }
                    }
                };
            }else {
                request.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        const str = 'onreadystatechange ->  ' + this;
                        debug([str, this.responseText]);
                        this.target.onReady();
                    }
                };
            }
            const phpForm = 'controller/' + event.target.phpIdentifier + '.php';
            request.open("POST", phpForm, true);
            if(event.target.phpIdentifier === 'uploadImage') {
                const saveBtn = document.getElementById('uploadImageDialog_save');
                const cancelBtn = document.getElementById('uploadImageDialog_cancel');
                if(data){
                    saveBtn.disabled = true;
                    cancelBtn.disabled = true;
                    event.target.style.display = 'none';
                    request.send(data);
                }
            } else if (event.target.phpIdentifier === 'contactSupportTeam') {
                // contactSupportTeam
                const saveBtn = document.getElementById('contactSupportTeamDialog_save');
                const cancelBtn = document.getElementById('contactSupportTeamDialog_cancel');
                if(data){
                    saveBtn.disabled = true;
                    cancelBtn.disabled = true;
                    event.target.style.display = 'none';
                    request.send(data);
                }
            } else {
                request.setRequestHeader( "Content-Type", "application/json; charset=UTF-8");
                request.send(JSON.stringify(dataObj));
            }

        });
    }

}
function onTransitionEnd(event) {
    // first definition should be empty
}

function showHideSubmenu(id, show) {
    const targetSubmenu = document.getElementById('subMenu_' + id);
    // console.log('showHideSubmenu -> ', id, targetSubmenu);
    if(show === 'show' && targetSubmenu) {
        visibleSubMenu = targetSubmenu;
        visibleSubMenu.style.display = 'block';
    } else if (visibleSubMenu != ''){
        visibleSubMenu.style.display = 'none';
    }
}
function dispatchCustomEvent(item, event) {
    const evt = document.createEvent("HTMLEvents");
    evt.initEvent(event, false, true);
    item.dispatchEvent(evt);
}
function openClose(clip, destination, scope, dialogHeader, id = null) {
    if(scope === 'deleteIssue') {

        const answer = window.confirm("ACHTUNG! Diese Funktion ist mit Bedacht zu nutzen\nund kann nicht rückgängig gemacht werden!");
        if (answer) {
        } else {
            return;
        }
    }
    setDialogHeader(clip, dialogHeader);
    const targetForm = document.getElementById(scope + 'DialogForm');
    // console.log('openClose -> ', targetForm, scope, id)
    if(!formDefined && targetForm) {
        // defineForm('addArticleDialogForm', 'addArticle');
        defineForm('addIssueDialogForm','addIssue');
        defineForm('deleteIssueDialogForm', 'deleteIssue');
        defineForm('contactSupportTeamDialogForm', 'contactSupportTeam', closeDialog);
        if(scope === 'uploadImage'){
            defineForm('uploadImageDialogForm', 'uploadImage', closeDialog);
        }
        formDefined = true;
    }

    if(id && scope === 'uploadImage') {
        document.getElementById('progressDivId').style.display = 'none';
        const element = document.getElementById('theme_' + id);
        document.getElementById('pageId').value = id;
        document.getElementById('affectedPages').value = element.inArticlePages.length;
        const magazineId = document.getElementById(scope + 'MagazineId').value;
        const issueId = document.getElementById(scope + 'IssueId').value;
        const saveBtn = document.getElementById(scope + 'Dialog_save');
        const cancelBtn = document.getElementById(scope + 'Dialog_cancel');
        const fileInput = document.getElementById(scope + 'DialogFormUploadFile');
        fileInput.value = '';
        dispatchCustomEvent(fileInput, 'change');
        targetForm.magazineId = magazineId;
        targetForm.issueId = issueId;
        saveBtn.disabled = false;
        cancelBtn.disabled = false;
    } else if (scope === 'contactSupportTeam') {
        const saveBtn = document.getElementById(scope + 'Dialog_save');
        const cancelBtn = document.getElementById(scope + 'Dialog_cancel');
        const userMessage = document.getElementById('userMessage');
        const fileInput = document.getElementById(scope + 'DialogFormUploadFile');
        userMessage.onfocus = function (event) { avoidDrag(event)};
        fileInput.value = '';
        dispatchCustomEvent(fileInput, 'change');

        userMessage.value = '';
        userMessage.innerHtml = '';
        saveBtn.disabled = false;
        cancelBtn.disabled = false;
    } else if(scope === 'addUser' || scope === 'editUser'){

        const userNameField = document.getElementById('userName' + capitalize(scope));
        const userEmailField = document.getElementById('email' + capitalize(scope));
        const userPasswordField = document.getElementById('password' + capitalize(scope));
        if(scope === 'addUser') {
            userNameField.value = '';
            userNameField.innerHtml = '';
            userEmailField.value = '';
            userEmailField.innerHtml = '';
            userPasswordField.value = '';
            userPasswordField.innerHtml = '';
        }
        userNameField.onfocus = function (event) { avoidDrag(event)};
        userEmailField.onfocus = function (event) { avoidDrag(event)};
        userPasswordField.onfocus = function (event) { avoidDrag(event)};
    }

    const targetClip = document.getElementById(clip);
    const forms = targetClip.getElementsByClassName('form');
    for(let i in forms) {
        if(forms[i].id && forms[i] != targetForm) {
            forms[i].style.display = 'none';
        }
    }
    if(targetForm) {
        targetForm.style.display = 'block';
        visibleForm = targetForm;
    }

    targetClip.style.display = 'block';
    targetClip.style.transform = 'scale(0.1)';
    targetClip.style.opacity = '1';
    targetClip.style.transition = 'all .2s ease-in-out';
    // document.addEventListener("mousemove", resize, false);
    // document.removeEventListener("mousemove", resize, false);
    targetClip.addEventListener('transitionend', (event) => {
        onTransitionEnd(event);
    });
    let timeout;
    if (destination === 'open') {
        openDialogElement = targetClip;
        timeout = setTimeout(openDialog,10);
    } else {
        timeout = setTimeout(closeDialog, 10);
    }

}
function getId(item) {
    return Number(item.id.substr(item.id.lastIndexOf('_') + 1, item.id.length));
}

function getTitle(item) {
    // console.log('value -> ', item.getElementsByClassName('theme-content-title')[0].value);
    // console.log('innerHTML -> ',item.getElementsByClassName('theme-content-title')[0].innerHTML);
    return item.getElementsByClassName('theme-content-title')[0].value.replace(/[&*<>,]/g, ' ');
}
function getAdTitle(item) {
    // console.log('value -> ', item.getElementsByClassName('theme-ad-title')[0].value);
    // console.log('innerHTML -> ',item.getElementsByClassName('theme-ad-title')[0].innerHTML);
    return item.getElementsByClassName('theme-ad-title')[0].value.replace(/[&*<>,]/g, ' ');
}

function createUpdateArticleQuery(pageId, contentId, titleTxt, articleStatus, articleStatusMailSent, issueId, magazineId, year, backgroundImage, layoutFile) {
    const backgroundTag = backgroundImage === 'NULL' ? ', `background_image`=' + backgroundImage + '' : '';
    const layoutTag = layoutFile === 'NULL' ? ', `layout_file`=' + layoutFile + '' : '';
    return 'UPDATE `pages` SET `content_id`=' + contentId + ', `title`="' + titleTxt + '", `article_status`="' + articleStatus + '" '+ backgroundTag +''+ layoutTag +' WHERE `page_id`=' + pageId + ' AND `issue_id`=' + issueId + ' AND `magazine_id`=' + magazineId + ' AND `year`=' + year + ';';
}
function createUpdateAdQuery(pageId, adId, adTxt, adStatus, adStatusMailSent, hasAd, adType, issueId, magazineId, year) {
    return 'UPDATE `pages` SET `ad_id`=' + adId + ', `ad_title`="' + adTxt + '", `has_ad`="' + hasAd + '", `ad_type`="' + adType + '", `ad_status`="' + adStatus + '" WHERE `page_id`=' + pageId + ' AND `issue_id`=' + issueId + ' AND `magazine_id`=' + magazineId + ' AND `year`=' + year + ';';
}
function createAddUserQuery(name, email, password, permissions) {
    return 'INSERT INTO `fe_user` (`name`, `email`,`password`,`permissions`) VALUES ("' + name + '","' + email + '","' + password + '","' + permissions+ '");';
    // return 'UPDATE `pages` SET `ad_id`=' + adId + ', `ad_title`="' + adTxt + '", `has_ad`="' + hasAd + '", `ad_type`="' + adType + '", `ad_status`="' + adStatus + '" WHERE `page_id`=' + pageId + ' AND `issue_id`=' + issueId + ' AND `magazine_id`=' + magazineId + ';';
}
function createEditUserQuery(name, email, password, permissions) {
    const pw = password? ',`password`="' + password + '"' : '';
    return 'UPDATE `fe_user` SET `name`="' + name + '"' + pw + ', `permissions`="' + permissions + '" WHERE `email`="' + email + '";';
    // return 'UPDATE `pages` SET `ad_id`=' + adId + ', `ad_title`="' + adTxt + '", `has_ad`="' + hasAd + '", `ad_type`="' + adType + '", `ad_status`="' + adStatus + '" WHERE `page_id`=' + pageId + ' AND `issue_id`=' + issueId + ' AND `magazine_id`=' + magazineId + ';';
}
function encode_utf8(string) {
    // console.log('encode_utf8 -> ', unescape(encodeURIComponent(string)));
    return unescape(encodeURIComponent(string));
}

function decode_utf8(string) {
    // console.log('decode_utf8 -> ', decodeURIComponent(escape(string)));
    return decodeURIComponent(escape(string));
}
function saveArticle(magazineId, issueId, notification=false) {
    const actualYear = getSetIssueYear().year;
    let contentId = 0;
    let contentTitle = '';
    let adId = 0;
    let adTitle = '';
    let adClass = '';
    let adStatus = '';
    let articleStatus = '';
    let pageId = 0;
    let query = '';
    let queryArray = [];
    for (let i in pageArray) {
        const item = pageArray[i][0].parentNode;
        // article

        contentTitle = getTitle(item);
        if(item.hasArticle && contentTitle && !item.isSubElement) {
            contentId = getId(item);
            articleStatus = item.articleStatus;
            query = createUpdateArticleQuery(contentId, contentId, contentTitle, articleStatus, '', issueId, magazineId,actualYear, '', '');
            queryArray.push(query);
            // console.log('hasArticleItem -> ', query, item);
            if (item.inArticlePages.length > 0) {
                for (let z in item.inArticlePages) {
                    if (item.inArticlePages[z].hasArticle && z > 0) {
                        pageId = getId(item.inArticlePages[z]);
                        query = createUpdateArticleQuery(pageId, contentId, '', articleStatus, '', issueId, magazineId, actualYear, '', '');
                        queryArray.push(query);
                        // console.log('inArticlePages -> ', query, item.inArticlePages[z]);
                    }
                }
            }
        } else if(!item.hasArticle) {
            contentId = getId(item);
            // const NULL = null;
            query = createUpdateArticleQuery(contentId, contentId, '', '','', issueId, magazineId, actualYear, 'NULL', 'NULL');
            queryArray.push(query);
        }
        // ads
        if(item.inAdPages.length > 0 && item.hasAd) {
            adId = getId(item);
            adTitle = getAdTitle(item);
            adClass = item.adClass;
            adStatus = item.adStatus;
            // console.log('ad -> ', adId, adId, adTitle, adStatus, true, adClass, issueId, magazineId);
            // todo define adType out of class
            query = createUpdateAdQuery(adId, adId, adTitle, adStatus, '',true, adClass, issueId, magazineId, actualYear);
            queryArray.push(query);
            for(let z = 1; z < item.inAdPages.length; z++){
                if(item.inAdPages[z].hasAd) {
                    pageId = getId(item.inAdPages[z]);
                    adClass = item.inAdPages[z].adClass;
                    // todo define adType out of class
                    query = createUpdateAdQuery(pageId, adId, adTitle, adStatus, '', true, adClass, issueId, magazineId, actualYear);
                    queryArray.push(query);
                }
            }
        } else if(!item.hasAd) {
            // reset items
            adId = getId(item);
            query = createUpdateAdQuery(adId,adId,'', '','', false,'none',issueId, magazineId, actualYear);
            queryArray.push(query);
        }
    }
    if(queryArray.length > 0) {
        // console.log({queryArray});
        sendDataToPhp(queryArray, 'controller/addThemes.php', magazineId, issueId, notification);
    }
}
function sendDataToPhp(data, php, magazineId, issueId, notification=false) {
    const request = new XMLHttpRequest();
    request.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            const str = 'onreadystatechange ->  ' + this;
            const additionalString = str + this.responseText;
            if(notification) {
                alert('Erfolgreich gespeichert.');
                addValuesToNotificationForm(magazineId, issueId);
            }
            debug([this.responseText]);
        }
    };
    request.open("POST", php);
    request.setRequestHeader( "Content-Type", "application/json; charset=UTF-8");
    request.send(JSON.stringify(data));
}
function resizeActiveLineBreakClips(array, value) {
    if(array.length > 0) {
        for (let i in array){
            const activeLineBreakCLip = array[i].clip;
            const activeLineBreakCLipX = array[i].left;
            const relatingArticle = array[i].article;
            // const activeLineBreakCLipY = array[i].top;
            if(typeof value === 'string') {
                relatingArticle.style.width = textFieldInitialValue;
                activeLineBreakCLip.style.width = value;
            } else {
                const activeLineBreakCLipWidth = (value - activeLineBreakCLipX) - 10;
                activeLineBreakCLip.style.width = activeLineBreakCLipWidth + 'px';
            }

        }
    }
}
function resize(event){
    const resizeElement = scaleTarget; // document.getElementById("theme-content_6");
    const pageElement = resizeElement.parentNode;
    const pageId = getId(pageElement);
    // const resizeAble = checkScalePageHit(scaleTarget, event);
    const parent = resizeElement; // .parentNode;
    let deltaX = event.x - m_pos;
    m_pos = event.x;

    const targetWidth = (parseInt(getComputedStyle(parent, '').width) + deltaX);
    const stage = document.getElementById('stage');

    if(parentStageRefX + targetWidth > (parentStageWidth)) {
        windowResized = true;
        const diffWidth = parentStageRefX + targetWidth + 30;
        stage.style.width = diffWidth + 'px';
        stage.style.left = -(diffWidth - parentStageWidth) + 'px';
        // console.log('in resize ', stage.offsetWidth);
        resizeActiveLineBreakClips(activeLineBreakClipArray, diffWidth);
        setAbsolute();
    } else {
        if(prevActiveLineBreakClipArray.length > 0 && activeLineBreakClipArray != prevActiveLineBreakClipArray){
            // console.log('has prevActiveLineBreakClipArray -> ', prevActiveLineBreakClipArray);
            for(let n in prevActiveLineBreakClipArray) {
                if (pageId < prevActiveLineBreakClipArray[n].id && pageElement.offsetTop === prevActiveLineBreakClipArray[n].clip.offsetTop) {
                    const nextClip = prevActiveLineBreakClipArray[n].clip;
                    const count = Math.floor((parentStageWidth - (parentStageRefX + targetWidth)) / (pageElement.offsetWidth));
                    if (parentStageRefX + targetWidth > prevActiveLineBreakClipArray[n].clip.offsetLeft + pageElement.offsetWidth){
                        windowResized = true;
                        nextClip.style.width = 'initial'; // pageElement.offsetWidth + 'px';
                        const clip = document.getElementById('theme_' + (prevActiveLineBreakClipArray[n].id + 1));
                        if(count > 1) {
                            clip.style.width = (parentStageWidth - (prevActiveLineBreakClipArray[n].clip.offsetLeft + pageElement.offsetWidth)) - 10 + 'px';
                        }

                        const x = clip.offsetTop;
                        const y = clip.offsetLeft;
                        const articleContainer = clip.content.textField;
                        const newRightClip = {clip: clip, id: (prevActiveLineBreakClipArray[n].id + 1), left: x, top: y, article: articleContainer};
                        prevActiveLineBreakClipArray[n] = newRightClip;

                        break;
                    }
                }
            }
        }
        if(windowResized) {
            setAbsolute();
        }

    }
    //console.log(targetWidth);
    if(targetWidth <= maxArticleWidth && targetWidth >= minArticleWidth) {
        parent.style.width = targetWidth + "px";
    }
}
function changeTextField (event) {
    const contentElement = event.target.parentNode;
    const contentElementContainer = event.target.parentNode.parentNode;
    const elementType = contentElement.id.substr(0,contentElement.id.indexOf('-'));
    const id = getId(contentElement);
    if(elementType === 'ad') {
        adInfoArray[id][0] = event.target.value;
    } else {
        articleInfoArray[contentElementContainer.articleId][0] = event.target.value;
    }

}
function showHideActiveTitleTextField(textField, value) {
    if(textField) {
        textField.addEventListener('change', function (event) {
            changeTextField(event);
            event.target.removeEventListener('change', changeTextField, false);
        });
        textField.style.width = value? '100%' : '1px';
        textField.style.display = value? 'block' : 'none';
        if(value > 0) {
            if(textField.value) {
                textField.focus();
                textField.setSelectionRange(0, textField.value.length);
            } else {
                textField.focus();
            }
        }
        // console.log('showHideActiveTitleTextField -> ', textField);
    }
}
function removeAd(id) {
    const item = document.getElementById('theme_' + id);
    if(item && item.inAdPages.length > 0) {
        // console.log('remove ad -> ',item, item.inAdPages);
        item.hasNeighbour = false;
        item.neighbour = null;
        for(let z in item.inAdPages){
            // console.log('remove ad -> ',item.inAdPages[z]);
            item.inAdPages[z].hasAd = false;
        }
    }
    item.inAdPages = [];
    item.hasAd = false;
    item.childNodes[1].style.display = 'none';
    calcPagePercentage();
}
function uploadArticle(id) {
    alert('upload at page ' + id);
}
function deleteLayoutData(magazineId, issueId, year, articleId) {
    const data = new FormData();
    data.append('year', year);
    data.append('magazine', magazineId);
    data.append('issue', issueId);
    data.append('article', articleId);

    const request = new XMLHttpRequest();
    request.magazineId = magazineId;
    request.issueId = issueId;
    request.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            saveArticle(this.magazineId,this.issueId,true);
            // window.location.reload();
            // alert('Benachrichtigungen Verschickt.');
            // osAjaxBackend.BindAfterAjaxRequest(function(){alert('AJAX refresh')});
            debug([this.responseText]);
        }
    };
    request.open("POST", 'controller/deleteLayoutData.php', true);
    // request.setRequestHeader( "Content-Type", "application/json; charset=UTF-8");
    request.send(data);
}
function removeArticle(id) {

    const item = document.getElementById('theme_' + id);
    const tmpArticleId = item.articleId;

    item.isMainElement = false;
    item.isSubElement = false;
    item.content.article.value = '';
    item.content.article.innerHtml = '';
    item.content.textField.style.width = textFieldInitialValue;
    // console.log('removeArticle -> ', item.inArticlePages);
    if(item.content.subElement) {
        item.content.subElement.content.textField.removeEventListener("mousedown", initTextFieldMouseEvent , false);
    }
    if(item && item.inArticlePages.length > 0) {
        for(let z in item.inArticlePages){
            // console.log(item.inArticlePages[z]);
            item.inArticlePages[z].content.article.value = '';
            item.inArticlePages[z].content.article.innerHtml = '';
            item.inArticlePages[z].content.textField.style.width = textFieldInitialValue;
            if(item.inArticlePages[z].content.close) {
                item.inArticlePages[z].content.close.style.display = 'block';
            }

            item.inArticlePages[z].content.statusBar.style.display = 'block';
            item.inArticlePages[z].content.textField.style.display = 'none';
            if(item.inArticlePages[z].content.uploadButton) {
                item.inArticlePages[z].content.uploadButton.style.display = 'none';
            }


            item.inArticlePages[z].content.mainElement = null;
            item.inArticlePages[z].content.subElement = null;
            item.inArticlePages[z].hasArticle = false;
            item.inArticlePages[z].isSubElement = false;
            item.inArticlePages[z].articleId = null;
        }
    }
    item.content.textField.removeEventListener("mousedown", initTextFieldMouseEvent , false);
    item.content.mainElement = null;
    item.content.subElement = null;
    // item.style.width = (item.lastChild.offsetWidth) + 'px';
    item.hasArticle = false;
    item.inArticlePages = [];
    item.content.textField.style.display = 'none';
    calcPagePercentage();
    if(item.layout) {
        const answer = window.confirm("Dieser Artikel enthält Layout-Daten.\nSollen diese ebenfalls gelöscht werden?");
        if (answer) {
            const magazineId = getSetMagazine().magazine;
            const issueId = getSetIssueYear().issue;
            const year = getSetIssueYear().year;
            deleteLayoutData(magazineId, issueId, year, tmpArticleId);
        } else {
            // return;
        }
    }
}
function initResize(event, item) {

    const referenceClip = item.parentNode;
    const refId = getId(referenceClip);
    const clip = { name: referenceClip, id: refId, x: referenceClip.offsetLeft, y: referenceClip.offsetTop };
    setStageSize('auto', clip);
    /*
    if(referenceClip && referenceClip.inArticlePages.length > 0) {
        for(let z in referenceClip.inArticlePages){
            if(z > refId && referenceClip.articleId === referenceClip.inArticlePages[z].articleId) {
                // referenceClip.inArticlePages[z].articleId = null;
                // referenceClip.inArticlePages[z].hasArticle = false;
            }
        }
    }
     */
    if(!item.initialized) {
        minArticleWidth = item.offsetWidth;
        item.initialized = true;
        item.minWidth = item.offsetWidth;
    }
    minArticleWidth = referenceClip.offsetWidth - 11;
    maxArticleWidth = -item.offsetLeft;
    // console.log('check maxArticleWidth -> ',item);
    for(let i = (refId); i < pageArray.length; i++ ){
        let checkRightContainer = pageArray[i][0].parentNode;
        maxArticleWidth += checkRightContainer.content.background.offsetWidth + getElementMargin(checkRightContainer);
        // console.log('initResize -> ', maxArticleWidth);
        if(checkRightContainer.hasArticle || checkRightContainer.isCover) {
            if(checkRightContainer.articleId != referenceClip.articleId) {
                maxArticleWidth -= 11;
                break;
            }
        }
    }
    m_pos = event.x;
    scaleTarget = item;
    item.origWidth = item.offsetWidth;
}

function blockHiddenPages(event, id) {

    showHideSubmenu(null, 'hide');
    const item = document.getElementById('theme_' + id);
    if (item && item.inArticlePages.length > 0) {
        for (let z in item.inArticlePages) {
            if(z > 0){
                item.inArticlePages[z].hasArticle = false;
                item.inArticlePages[z].articleId = null;
            }
        }
        item.inArticlePages = [];
    }
    for (let i = 1; i < pageArray.length; i++) {
        const clip = pageArray[i][0].parentNode;
        const textField = clip.content.textField;
        const clipX = clip.offsetLeft;
        let articleArray;
        if (textField.style.display === 'block') {
            if (!clip.isSubElement) {
                clip.inArticlePages = [clip];
                articleArray = clip.inArticlePages;
            } else {
                articleArray = clip.content.mainElement.inArticlePages;
                articleArray.push(clip);
            }
            const refY = clip.offsetTop;
            for (let z = i + 1; z < pageArray.length; z++) {
                const rightElement = pageArray[z][0].parentNode;
                const rightElementY = rightElement.offsetTop;
                const rightElementX = rightElement.offsetLeft;
                if (rightElementY === refY) {
                    if (rightElementX < clipX + textField.offsetWidth) {
                        // console.log(clip, rightElement, textField);
                        articleArray.push(rightElement);
                        rightElement.hasArticle = true;
                        rightElement.articleId = clip.articleId;
                    }
                } else {
                    break;
                }
            }
        }
    }
    document.removeEventListener("mousemove", resize, false);
    // console.log('blockHiddenPages ', item.inArticlePages);
}
function adMouseDown(id, handler) {
    activeTitleTextField = handler.parentNode.childNodes[1];
    showHideActiveTitleTextField(activeTitleTextField, 0);
}

function calcItemPercentage(item, typeOfContent) {
    const id = getId(item);
    const statusBar = document.getElementById('articleStatusSelect_' + id);// document.getElementById('articleStatus_' + id).firstChild.firstChild;
    let statusLevel = 0;
    let selectedStatus = '';
    let percentage = 0;
    if(statusBar.id && item.hasArticle && typeOfContent == 'article' ) {
        for(let i in statusBar) {
            if (statusBar[i].selected) {
                statusLevel = Number(i) + 1;
                selectedStatus = statusBar[i].value;
                break;
            }
        }
        percentage = Math.floor((statusLevel / item.articlePercentageQuotient)*100); // statusLevel > 1 ? Math.floor((statusLevel / item.articlePercentageQuotient)*100) : 0;
        // statusBar.id &&
    } else if(item.hasAd && typeOfContent == 'ad') {
        /*
        for(let i in statusBar) {
            if (statusBar[i].selected) {
                statusLevel = Number(i) + 1;
                selectedStatus = statusBar[i].value;
                break;
            }
        }
         */
        percentage = 100; // statusLevel > 1 ? Math.floor((statusLevel / item.articlePercentageQuotient)*100) : 0;
    }
    item.statusPercentage = percentage;
    return percentage;
}
function getGreenToRed(percent){
    const greenMax = 155;
    const redMax = 255;
    const r = percent<50 ? redMax : Math.floor(redMax-(percent*2-100)*redMax/100);
    const g = percent>50 ? greenMax : Math.floor((percent*2)*greenMax/100);
    return 'rgb('+r+','+g+',0)';
}
function calcPagePercentage() {
    let pages = 0;
    let adPages = 0;
    let articlePages = 0;
    let articlePercentage = 0;
    let adPercentage = 0;
    let percentageAtAllMax = 0;
    let maxPages = pageArray.length;
    let maxPossiblePages = maxPages - 2;
    let pagePercentage = 0;
    // console.log(pageArray.length);
    for(let i = 1; i < maxPages-1; i++) {
        const item = pageArray[i][0].parentNode;//pageArray[i];
        // console.log({item});
        if(item.id) {
            percentageAtAllMax += 100;
            // console.log(item);
            pages ++;
            if(item.hasAd) {
                adPages ++;
                adPercentage += calcItemPercentage(item, 'ad');
                // console.log(adPercentage);
            }
            if(item.hasArticle) {
                articlePages ++;
                articlePercentage += calcItemPercentage(item, 'article');
                // console.log(articlePercentage);
            }
            if(item.hasAd && item.hasArticle) {
                const averagePercentage = calcItemPercentage(item, 'ad') + calcItemPercentage(item, 'article');
                pagePercentage += averagePercentage / 2;
                // console.log(item, ' has both ', averagePercentage / 2);
            } else if(item.hasAd && !item.hasArticle) {
                const averagePercentage = calcItemPercentage(item, 'ad');
                pagePercentage += averagePercentage;
                // console.log(item, ' has ad ', averagePercentage);
            } else if(item.hasArticle && !item.hasAd) {
                const averagePercentage = calcItemPercentage(item, 'article');
                pagePercentage += averagePercentage;
                // console.log(item, ' has article ', averagePercentage);
            }
        }
    }
    const allArticlePercentage = Math.floor(articlePercentage / articlePages) + '%';
    const allAdPercentage = Math.floor(adPercentage / adPages) + '%';
    const statusBarStatus = document.getElementById('statusBarProgressStatus');
    // const statusBarContainer = document.getElementById('statusBarProgressContainer');

    const a = Math.floor((pagePercentage / percentageAtAllMax) * 100);
    const bgColor = getGreenToRed(a); //'hsl(' + getGreenToRed(a) + ',100%,50%)';
    if(statusBarStatus) {
        statusBarStatus.style.width = a + '%';
        statusBarStatus.innerHTML = a + '%';
        statusBarStatus.style.backgroundColor = bgColor;
    }
    // console.log('bgColor -> ', bgColor, 'article -> ', allArticlePercentage, ' ads -> ', allAdPercentage, 'max aviable percentage -> ', percentageAtAllMax, 'pages ->', maxPossiblePages, 'Sum of pagePercentage -> ', pagePercentage);
}
function getElementMargin(item) {
    let width = 0;
    let marginRight = 0;
    let marginLeft = 0;
    if(item) {
        marginRight = parseInt(getComputedStyle(item).marginRight, 10);
        marginLeft = parseInt(getComputedStyle(item).marginLeft, 10);
    }
    width = marginRight + marginLeft;
    return width;
}
function getAllCLipElements(elem) {
    const id = getId(elem);
    const statusBar = document.getElementById('articleStatus_' + id).firstChild;
    const close = document.getElementById('theme-close_' + id);
    const uploadButton = document.getElementById('articleUpload_' + id);
    const article = document.getElementById('theme-content-title_' + id);
    const textField = document.getElementById('theme-content_' + id);
    const background = document.getElementById('page_' + id);
    const newElem = {uploadButton: uploadButton ? uploadButton : null, statusBar: statusBar, close: close, article: article, textField: textField, background: background , referenceClip: elem, subElement: null, mainElement: null};
    return newElem;
}
function synchronizeTextFields(event) {

    const mainElement = event.target.parentNode.parentNode.content.mainElement;
    const subElement = event.target.parentNode.parentNode;
    //console.log('artcielId textContainer', subElement.articleId, 'articleId Maincontainer', mainElement.articleId)
    // console.log('synchronize TextField', event, mainElement);
    if(event.target.tagName === 'TEXTAREA') {
        const value = event.target.value;
        // console.log('newValue ', value, 'oldValue ', mainElement.content.article.value);
        if(mainElement) {
            mainElement.content.article.value = value;
        }
    }
}
function initTextFieldMouseEvent(event) {
    if (event.target.tagName === 'TEXTAREA') {
        const mainElement = event.target.parentNode.parentNode;
        const isMainElement = mainElement.isMainElement;
        if(isMainElement) {
           // console.log('hasNewline', mainElement.content.newLineElement);
            mainElement.content.subElement.content.textField.style.display = 'none';
            mainElement.content.subElement.hasArticle = false;
            mainElement.content.subElement.content.textField.value = '';
            mainElement.content.subElement.content.mainElement = null;
            if(mainElement.content.subElement.content.close) {
                mainElement.content.subElement.content.close.style.display = 'block';
            }

            if(mainElement.content.subElement.content.uploadButton) {
                mainElement.content.subElement.content.uploadButton.style.display = 'block';
            }
            mainElement.content.statusBar.style.display = 'block';
        }
        activeTitleTextField = event.target; // this.getElementsByClassName('theme-content-title')[0];
        // console.log('mousedown themeContent -> ', activeTitleTextField, event.target);
        showHideActiveTitleTextField(activeTitleTextField, 0);
        initResize(event, event.target.parentNode);
        document.addEventListener("mousemove", resize, false);
    }
}
function isTouchDevice() {
    return (('ontouchstart' in window) ||
        (navigator.maxTouchPoints > 0) ||
        (navigator.msMaxTouchPoints > 0));
}


function setAbsolute() {
    // checkTouchDevice
    // alert('isMobile -> ' + isTouchDevice());
    isMobile = isTouchDevice();
    //let double = false;
    // ad settings
    minArticleWidth = 0;
    for(let pages = 0;  pages < themesArray.length;  pages++) {
        const clip = themesArray[pages];
        if(pages === 0 || pages === 1 || pages === themesArray.length-2 || pages === themesArray.length-1) {
            clip.isCover = true;
            if(pages === 0 || pages === themesArray.length-1){
                if(pages === 0) {
                    clip.style.marginRight = clip.content.background.offsetWidth + 5 + 'px';
                } else {
                    clip.style.marginLeft = clip.content.background.offsetWidth + 'px';
                }

            }
        }
        if(pages === themesArray.length-1) {
            getElement('stage').style.height = clip.offsetTop + clip.offsetHeight + 2 + 'px';
            // console.log('height of Stage -> ',clip, clip.offsetTop + clip.offsetHeight,  getOffset(clip).bottom);
        }
    }
    for(let ad = 0; ad < themesArray.length; ad++) {
        const clip = themesArray[ad];
        const adContent = document.getElementById('ad-content_' + (ad+1));
        if(clip.hasAd) {
            calcItemPercentage(clip, 'ad');
            adContent.style.display = 'block';
            if(adContent.parentNode.content.close) {
                adContent.parentNode.content.close.style.display = 'block';
            }

            getAdBackground(adContent).classList.add(clip.adClass);
            const adTitleClip = adContent.getElementsByClassName('theme-ad-title')[0];
            adTitleClip.style.display = 'block';
            const refAdId = clip.adId;
            clip.inAdPages.push(clip);
            if((ad+1) < themesArray.length - 1) {
                const checkNextCLip = themesArray[ad + 1];
                const checkNextCLipAdId = checkNextCLip.hasAd ? checkNextCLip.adId : 0;
                if (refAdId === checkNextCLipAdId) {
                    if(!clip.inAdPages.includes(checkNextCLip)) {
                        clip.inAdPages.push(checkNextCLip);
                        // TODO check adWidth here -- noticed as possible source of width-bug at 23-02-17
                        adContent.style.width = (2 * adContent.offsetWidth) + 'px';
                    }
                    ad += 1;
                }
            }
        }
    }
    // article settings
    for(let article = 0; article < themesArray.length; article++) {
        const clip = themesArray[article];
        const themeContent = document.getElementById('theme-content_' + (article+1));
        // const adContent = document.getElementById('ad-content_' + (article+1));

        if(clip.hasArticle) {
            const titleClip = themeContent.getElementsByClassName('theme-content-title')[0];
            // const adTitleClip = adContent.getElementsByClassName('theme-ad-title')[0];
            calcItemPercentage(clip, 'article');
            titleClip.style.display = 'block';
            // adTitleClip.style.display = 'block';
            const refArticleId = clip.articleId;
            const targetClip = clip;

            themeContent.initialized = true;
            targetClip.isMainElement = false;
            targetClip.isSubElement = false;
            targetClip.content.textField.style.borderRight = '';
            targetClip.inArticlePages = [];
            targetClip.inArticlePages.push(targetClip);
            // console.log('article TargetClip -> ', targetClip.articleId, article);
            // fill in article pages Array //
            for(let z = (article+1); z < themesArray.length; z++) {
                const rightClip = themesArray[z];
                if (refArticleId === rightClip.articleId){
                    // rightClip.hasArticle = true;
                    rightClip.content.textField.style.display = 'none';
                    targetClip.inArticlePages.push(rightClip);
                } else if(refArticleId != rightClip.articleId) {
                    article = z - 1;
                    break;
                }
            }
            let textFieldWidth = targetClip.offsetWidth;
            let subTextFieldWidth = targetClip.offsetWidth;
            let subElement = null;
            targetClip.subElements = [];
            targetClip.content.article.classList.remove('article-transparent');
            targetClip.content.statusBar.style.display = 'block';
            textFieldWidth += getElementMargin(targetClip);

            minArticleWidth = textFieldWidth;

            for(let n in targetClip.inArticlePages) {
                const element = targetClip.inArticlePages[n];
                // console.log('place Elements', n);
                if(n === 0) { // same Element
                    textFieldWidth = element.content.background.offsetWidth;//  + margin;
                } else if(n > 0){
                    const leftElement = targetClip.inArticlePages[(n-1)];
                    if(element.offsetTop === targetClip.offsetTop){
                        textFieldWidth += getElementMargin(leftElement) + element.content.background.offsetWidth;
                    } else if(!targetClip.isMainElement) {
                        subElement = element;
                        targetClip.isMainElement = true;
                        targetClip.content.subElement = element;
                        subElement.content.mainElement = targetClip;
                        subElement.isSubElement = true;
                        subElement.content.referenceClip = targetClip;
                        subElement.content.textField.style.display = 'block';
                        if(subElement.content.close) {
                            subElement.content.close.style.display = 'none';
                        }

                        if(subElement.content.uploadButton) {
                            subElement.content.uploadButton.style.display = 'none';
                        }
                        subElement.content.article.value = targetClip.content.article.value;
                        subElement.content.article.style.display = 'block';
                        targetClip.content.statusBar.style.display = 'none';
                        textFieldWidth += 5;
                        subTextFieldWidth += 10;
                    } else {
                        subTextFieldWidth += element.content.background.offsetWidth;
                    }
                }
            }
            if(targetClip.inArticlePages.length > 1) {
                targetClip.content.textField.style.width = (textFieldWidth - 11) + 'px';
            }

            targetClip.content.textField.addEventListener("mousedown", initTextFieldMouseEvent , false);
            if(targetClip.isMainElement && subElement) {
                if(subElement.content.textField.style.display === 'block') {
                    const subWider = subTextFieldWidth > textFieldWidth;
                    // console.log('mainElementWidth ->', targetClip.id , textFieldWidth, 'subElementWidth ->', subElement.id, subTextFieldWidth, 'subWider = ', subWider);
                    targetClip.content.textField.style.borderRight = 'none';
                    // targetClip.content.article.style.opacity = subWider? '0' : '1';
                    if(subWider) {
                        targetClip.content.article.classList.add('article-transparent');
                        subElement.content.article.classList.remove('article-transparent');
                    } else {
                        targetClip.content.article.classList.remove('article-transparent');
                        subElement.content.article.classList.add('article-transparent');
                    }

                    subElement.content.textField.style.borderLeft = 'none';
                    // subElement.content.article.style.opacity = subWider? '1' : '0';
                    subElement.content.textField.style.left = '-1px';
                    subElement.content.textField.addEventListener("mousedown", initTextFieldMouseEvent , false);
                    subElement.content.textField.addEventListener("keyup", synchronizeTextFields, false);
                    subElement.content.textField.style.width = (subTextFieldWidth - 11) + 'px';
                }
            }
        }
    }

    document.addEventListener("mouseup", function(event){
        if(activeTitleTextField.value === '') {
            // console.log('mouseup -> ', event.target, activeTitleTextField.value);
            const page = getId(activeTitleTextField);
            const clickElement = event.target;
            if(getId(clickElement) != page && !activeTitleTextField.warning){
                const errorString = 'Bitte geben Sie einen Titel auf Seite ' + page + ' ein.';
                activeTitleTextField.warning = true;
                alert(errorString);
            }
        }
        const dialog = document.getElementById('editDialog');
        if (event.target.tagName != 'OPTION' && dialog.style.display != 'block' && dialog.style.opacity != '1') {
            setStageSize('70%');
            // console.log('mouseup -> without OPTIONS -> ', event.target);
            showHideActiveTitleTextField(activeTitleTextField, 1);

            // showHideActiveTitleTextField(activeAdTitleTextField, 1);
            const id = event.target.id.substr(event.target.id.indexOf('_') + 1, event.target.id.length);
            blockHiddenPages(event, id);
            maxArticleWidth = 0;
            document.removeEventListener("mousemove", resize, false);
        }
    }, false);
    clearTimeout(timeout);
    checkForDraggablePages();
    calcPagePercentage();
}
function initInputField() {
    const initialYearField = document.getElementById('actualIssueYear');
    initialYearField.value = getSetIssueYear().year;
    const inputs = document.getElementsByTagName( 'INPUT' );
    /// console.log(inputs);
    Array.prototype.forEach.call( inputs, function( input )
    {
        const label	 = input.nextElementSibling,
            labelVal = label? label.innerHTML:'';
        if(input.type==='file' && label) {
            input.addEventListener( 'change', function( e )
            {
                let fileName = '';
                const regex = /[^\\/]+?(?=\.\w+$)/

                // console.log('/path/to/file.png'.match(regex))
                if( this.files && this.files.length > 1 )
                    fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
                else
                    fileName = e.target.value.split( "'\'" ).pop();
                // console.log('filename -> ', fileName, label.getElementsByTagName( 'SPAN' )[0], e.target);
                if( fileName )
                    label.getElementsByTagName( 'SPAN' )[0].innerHTML = fileName.match(regex);
                else
                    label.innerHTML = labelVal;
            });
        }

    });
}
function initGlobalDragElements() {
    initInputField();
    // positioning of page Elements //
    // const mainContainer = document.getElementById('mainContainer');
    // const maxWidth = window.innerWidth;
    // mainContainer.style.width = 1100 + 'px';
    // const user = document['user'];
    const themes = document.getElementsByClassName('theme');
    const stage = document.getElementById('stage');


    for(let theme in themes) {
        const item = themes[theme];
        if(item.id) {
            themesArray.push(item);
            item.inArticlePages = new Array();
            item.inAdPages = new Array();
            const themeContent = item.childNodes[0].lastChild;
            item.statusPercentage = 0;
            item.isCover = false;
            item.isMainElement = false;
            item.isSubElement = false;
            item.hasArticle = item.childNodes[0].style.display === 'block';
            item.contentArray = themeContent.value.split(',');
            // console.log('themeContent ->',themeContent, {item});
            item.articleType = item.contentArray[0];
            item.adType = item.contentArray[1];
            item.adClass = item.contentArray[1];
            item.hasAd = item.contentArray[2] == 'true' ? true : false;
            item.articleTitle = item.contentArray[3];
            item.adId = Number(item.contentArray[9]);
            item.adTitle = item.contentArray[8];

            item.articleId = Number(item.contentArray[6]);
            const pageId = Number(theme) + 1;
            if(item.articleId < pageId) {
                item.hasArticle = true;
            }
            item.articleStatus = item.contentArray[10] ? item.contentArray[10] : 'null';
            item.adStatus = item.contentArray[5] ? item.contentArray[5] : 'null';
            item.articleSourceStatus = item.articleStatus ? item.articleStatus : 'null';
            item.adSourceStatus = item.adStatus ? item.adStatus : 'null';
            item.layout = item.contentArray[11] ? { hasLayout: true, source: item.contentArray[11] } : null;

            // console.log('item.articleSourceStatus -> ', item.articleSourceStatus, {item});
            // console.log(item.articleType, item.contentArray[11]);
            item.articlePercentageQuotient = Number(item.contentArray[12]);
            articlePercentageQuotient = Number(item.contentArray[12]);
            // item.contentArray[10];
            articleInfoArray.push([item.articleTitle, '', item.articleStatus, item.articleId]);
            adInfoArray.push([item.adTitle, item.adClass, item.adStatus]);
            item.content = getAllCLipElements(item);
            if(item.hasAd) {
                // console.log('adStatus -> ', item.contentArray[10], item.id);
            }
            if(item.hasArticle) {
                // console.log('articleStatus -> ', item.contentArray[5], item.id);
            }

            item.origX = getOffset(item).left-getOffset(stage).left;
            item.origY = getOffset(item).top-getOffset(stage).top;
            // item.style.left = item.origX + 'px';
            // item.style.top = item.origY + 'px';
        }
    }
    pageAmount = themesArray.length - 2;
    timeout = setTimeout(setAbsolute, 10);
}
function setDialogQuotient(window, value) {
    const html = document.getElementsByTagName('HTML')[0];
    const dialog = document.getElementById(window);
    if(html && dialog) {
        let x;
        if(getCookie(window)) {
            x = getCookie(window).split(',')[0];
        } else {
            x = getOffset(dialog).left;
        }
        if(window==='toolbox') {
            toolboxQuotient = x / html.offsetWidth;
            // console.log('page is fully loaded and toolbox is ready', 'quot -> ', toolboxQuotient);
        }
        if(window==='editDialog') {
            editDialogQuotient = x / html.offsetWidth;
            // console.log('page is fully loaded and toolbox is ready', 'quot -> ', editDialogQuotient);
        }
        if(window==='printDialog') {
            printDialogQuotient = x / html.offsetWidth;
            // console.log('page is fully loaded and toolbox is ready', 'quot -> ', printDialogQuotient);
        }
    }
}
function setDialogScalePosition(window, value) {
    const html = document.getElementsByTagName('HTML')[0];
    const dialog = document.getElementById(window);
    if(dialog && html) {
        if(dialog.style.display != 'none') {
            if(dialog.offsetLeft + dialog.offsetWidth > html.offsetWidth) {
                const left = html.offsetWidth - dialog.offsetWidth;
                dialog.style.left = left > 0 ? left + 'px' : '';
                const x = getOffset(dialog).left;
                const y = getOffset(dialog).top;
                setCookie(window, [x, y], 30);

            }
            const x = getOffset(dialog).left;
            const quot = x / html.offsetWidth;
            const leftNew = dialog.offsetLeft * (value/quot);
            dialog.style.left = leftNew > 0 ? leftNew + 'px' : '';
        }
    }
}
window.addEventListener('load', (event) => {
    setDialogQuotient('toolbox', toolboxQuotient);
    setDialogQuotient('editDialog', editDialogQuotient);
    setDialogQuotient('printDialog', printDialogQuotient);
});
window.addEventListener('resize', (event) => {
    const html = document.getElementsByTagName('HTML')[0];
    if(html) {
        setAbsolute();
        setDialogScalePosition('toolbox', toolboxQuotient);
        setDialogScalePosition('editDialog', editDialogQuotient);
        setDialogScalePosition('printDialog', printDialogQuotient);
    }
});