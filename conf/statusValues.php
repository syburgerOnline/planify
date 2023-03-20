<?php
$GLOBALS['adStatusValues'] = array(
    1 => array(
        0 => array(
            'title' => 'geplant',
            'selected' => null,
            'notificationGroup' => [],
            'value' => "this, 'planned'",
            'function' => 'setStatusValue(#)',
            'permissionGroup' => [5,6]
        ),
        1 => array(
            'title' => 'Artikel abgegeben',
            'notificationGroup' => [],
            'selected' => null,
            'value' => "this, 'data-arrived'",
            'function' => 'setStatusValue(#)',
            'permissionGroup' => [5,6]
        ),
        2 => array(
            'title' => 'Bereit für Layout',
            'notificationGroup' => ['graphicGroup'],
            'selected' => null,
            'value' => "this, 'layout-ready'",
            'function' => 'setStatusValue(#)',
            'permissionGroup' => [3,5,6]
        ),
        3 => array(
            'title' => 'Layout in Arbeit',
            'notificationGroup' => [],
            'selected' => null,
            'value' => "this, 'layout-in-progress'",
            'function' => 'setStatusValue(#)',
            'permissionGroup' => [3,5,6]
        ),
        4 => array(
            'title' => 'Layout vorhanden',
            'notificationGroup' => ['editorInChiefGroup'],
            'selected' => null,
            'value' => "this, 'layout-arrived'",
            'function' => 'setStatusValue(#)',
            'permissionGroup' => [3,5,6]
        ),
        5 => array(
            'title' => 'Layout in Korrektur',
            'notificationGroup' => [],
            'selected' => null,
            'value' => "this, 'layout-review'",
            'function' => 'setStatusValue(#)',
            'permissionGroup' => [5,6]
        ),
        6 => array(
            'title' => 'Layout freigegeben',
            'notificationGroup' => ['graphicGroup','onlineGroup'],
            'selected' => null,
            'value' => "this, 'layout-final'",
            'function' => 'setStatusValue(#)',
            'permissionGroup' => [5,6]
        ),
    )
);
$GLOBALS['adStatusValues'][2] = $GLOBALS['adStatusValues'][1];
$GLOBALS['adStatusValues'][3] = $GLOBALS['adStatusValues'][1];
$GLOBALS['adStatusValues'][4] = $GLOBALS['adStatusValues'][1];
$GLOBALS['adStatusValues'][5] = $GLOBALS['adStatusValues'][1];
$GLOBALS['adStatusValues'][6] = $GLOBALS['adStatusValues'][1];
$GLOBALS['adStatusValues'][7] = $GLOBALS['adStatusValues'][1];