<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Workflow functions
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend classes
 * @version    1.8.1
 * @author     Timo Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


cInclude("includes", "functions.con.php");

function getUsers($listid, $default) {
    global $idclient, $cfg, $auth;

    $userlist = new Users;
    $users = $userlist->getAccessibleUsers(explode(',', $auth->auth['perm']));
    $grouplist = new Groups;
    $groups = $grouplist->getAccessibleGroups(explode(',', $auth->auth['perm']));

    $tpl2 = new Template;
    $tpl2->set('s', 'NAME', 'user' . $listid);
    $tpl2->set('s', 'CLASS', 'text_small');
    $tpl2->set('s', 'OPTIONS', 'size=1');

    $tpl2->set('d', 'VALUE', 0);
    $tpl2->set('d', 'CAPTION', '--- ' . i18n("None", "workflow") . ' ---');
    if ($default == 0) {
        $tpl2->set('d', 'SELECTED', 'SELECTED');
    } else {
        $tpl2->set('d', 'SELECTED', '');
    }
    $tpl2->next();

    if (is_array($users)) {

        foreach ($users as $key => $value) {

            $tpl2->set('d', 'VALUE', $key);
            $tpl2->set('d', 'CAPTION', $value["realname"] . " (" . $value["username"] . ")");

            if ($default == $key) {
                $tpl2->set('d', 'SELECTED', 'SELECTED');
            } else {
                $tpl2->set('d', 'SELECTED', '');
            }

            $tpl2->next();
        }
    }

    $tpl2->set('d', 'VALUE', '0');
    $tpl2->set('d', 'CAPTION', '------------------------------------');
    $tpl2->set('d', 'SELECTED', 'disabled');
    $tpl2->next();

    if (is_array($groups)) {

        foreach ($groups as $key => $value) {

            $tpl2->set('d', 'VALUE', $key);
            $tpl2->set('d', 'CAPTION', $value["groupname"]);

            if ($default == $key) {
                $tpl2->set('d', 'SELECTED', 'SELECTED');
            } else {
                $tpl2->set('d', 'SELECTED', '');
            }

            $tpl2->next();
        }
    }

    return $tpl2->generate($cfg['path']['templates'] . $cfg['templates']['generic_select'], true);
}

function isCurrentEditor($uid) {
    global $auth, $cfg;

    /* Check if the UID is a group. If yes, check if we are in it */
    $user = new User;
    if ($user->loadUserByUserID($uid) == false) {
        $db2 = new DB_ConLite;

        /* Yes, it's a group. Let's try to load the group members! */
        $sql = "SELECT user_id FROM "
                . $cfg["tab"]["groupmembers"] . "
                WHERE group_id = '" . Contenido_Security::escapeDB($uid, $db2) . "'";

        $db2->query($sql);

        while ($db2->next_record()) {
            if ($db2->f("user_id") == $auth->auth["uid"]) {
                return true;
            }
        }
    } else {
        if ($uid == $auth->auth["uid"]) {
            return true;
        }
    }

    return false;
}

function getActionSelect($idartlang, $idusersequence) {
    global $cfg;

    $workflowActions = new WorkflowActions;

    $allActions = $workflowActions->getAvailableWorkflowActions();

    $wfSelect = new Template;
    $wfSelect->set('s', 'NAME', 'wfselect' . $idartlang);
    $wfSelect->set('s', 'CLASS', 'text_medium');

    $userSequence = new WorkflowUserSequence;
    $userSequence->loadByPrimaryKey($idusersequence);

    $workflowItem = $userSequence->getWorkflowItem();

    if ($workflowItem === false) {
        return;
    }

    $wfRights = $workflowItem->getStepRights();

    $artAllocation = new WorkflowArtAllocations;
    $artAllocation->select("idartlang = '$idartlang'");

    if ($obj = $artAllocation->next()) {
        $laststep = $obj->get("lastusersequence");
    }

    $bExistOption = false;
    if ($laststep != $idusersequence) {
        $wfSelect->set('d', 'VALUE', 'next');
        $wfSelect->set('d', 'CAPTION', i18n("Confirm", "workflow"));
        $wfSelect->set('d', 'SELECTED', 'SELECTED');
        $wfSelect->next();
        $bExistOption = true;
    }

    if ($wfRights["last"] == true) {
        $wfSelect->set('d', 'VALUE', 'last');
        $wfSelect->set('d', 'CAPTION', i18n("Back to last editor", "workflow"));
        $wfSelect->set('d', 'SELECTED', '');
        $wfSelect->next();
        $bExistOption = true;
    }

    if ($wfRights["reject"] == true) {
        $wfSelect->set('d', 'VALUE', 'reject');
        $wfSelect->set('d', 'CAPTION', i18n("Reject article", "workflow"));
        $wfSelect->set('d', 'SELECTED', '');
        $wfSelect->next();
        $bExistOption = true;
    }

    if ($wfRights["revise"] == true) {
        $wfSelect->set('d', 'VALUE', 'revise');
        $wfSelect->set('d', 'CAPTION', i18n("Revise article", "workflow"));
        $wfSelect->set('d', 'SELECTED', '');
        $wfSelect->next();
        $bExistOption = true;
    }

    if ($bExistOption)
        return ($wfSelect->generate($cfg['path']['templates'] . $cfg['templates']['generic_select'], true));
    else {
        return false;
    }
}

#function for inserting todos in wokflow_art_allocation used, when a workflow is associated with a category in content->category

function setUserSequence($idartlang, $defaultidworkflow) {
    $wfaa = new WorkflowArtAllocations;
    $wfaa->select("idartlang = '$idartlang'");
    $idusersequence = 0;

    if ($associatedUserSequence = $wfaa->next()) {
        $idartallocation = $associatedUserSequence->get("idartallocation");
        $wfaa->delete($idartallocation);
    }

    if ($defaultidworkflow != -1) {
        $newObj = $wfaa->create($idartlang);

        if (!$newObj) {
            /* Try to load */
            $newObj = new WorkflowArtAllocation;

            echo $wfaa->lasterror;
            return false;
        }

        /* Get the first idusersequence for the new item */
        $workflowItems = new WorkflowItems;
        $workflowItems->select("idworkflow = '$defaultidworkflow' AND position = '1'");

        if ($obj = $workflowItems->next()) {
            $firstitem = $obj->get("idworkflowitem");
        }

        $workflowUserSequences = new WorkflowUserSequences;
        $workflowUserSequences->select("idworkflowitem = '$firstitem' AND position = '1'");

        if ($obj = $workflowUserSequences->next()) {
            $firstIDUserSequence = $obj->get("idusersequence");
        }

        $newObj->set("idusersequence", $firstIDUserSequence);
        $newObj->store();

        $idusersequence = $newObj->get("idusersequence");
        $associatedUserSequence = $newObj;
    }
}

function getCurrentUserSequence($idartlang, $defaultidworkflow) {
    $wfaa = new WorkflowArtAllocations;
    $wfaa->select("idartlang = '$idartlang'");
    $idusersequence = 0;

    if ($associatedUserSequence = $wfaa->next()) {
        $idusersequence = $associatedUserSequence->get("idusersequence");
    }

    if ($idusersequence == 0) {
        if ($associatedUserSequence != false) {
            $newObj = $associatedUserSequence;
        } else {
            $newObj = $wfaa->create($idartlang);

            if (!$newObj) {
                /* Try to load */
                $newObj = new WorkflowArtAllocation;

                echo $wfaa->lasterror;
                return false;
            }
        }

        /* Get the first idusersequence for the new item */
        $workflowItems = new WorkflowItems;
        $workflowItems->select("idworkflow = '$defaultidworkflow' AND position = '1'");

        if ($obj = $workflowItems->next()) {
            $firstitem = $obj->get("idworkflowitem");
        }

        $workflowUserSequences = new WorkflowUserSequences;
        $workflowUserSequences->select("idworkflowitem = '$firstitem' AND position = '1'");

        if ($obj = $workflowUserSequences->next()) {
            $firstIDUserSequence = $obj->get("idusersequence");
        }

        $newObj->set("idusersequence", $firstIDUserSequence);
        $newObj->store();

        $idusersequence = $newObj->get("idusersequence");
        $associatedUserSequence = $newObj;
    }

    return ($idusersequence);
}

function getLastWorkflowStatus($idartlang) {
    $wfaa = new WorkflowArtAllocations;

    $wfaa->select("idartlang = '$idartlang'");

    if ($associatedUserSequence = $wfaa->next()) {
        $laststatus = $associatedUserSequence->get("laststatus");
    } else {
        return false;
    }

    switch ($laststatus) {
        case "reject":
            return (i18n("Rejected", "workflow"));
            break;
        case "revise":
            return (i18n("Revised", "workflow"));
            break;
        case "last":
            return (i18n("Last", "workflow"));
            break;
        case "confirm":
            return (i18n("Confirmed", "workflow"));
            break;
        default:
            return (i18n("None", "workflow"));
            break;
    }
    return ("");
}

function doWorkflowAction($idartlang, $action) {
    global $cfg, $idcat;

    switch ($action) {
        case "last":
            $artAllocations = new WorkflowArtAllocations;
            $artAllocations->select("idartlang = '$idartlang'");

            if ($obj = $artAllocations->next()) {
                $usersequence = new WorkflowUserSequence;
                $usersequence->loadByPrimaryKey($obj->get("idusersequence"));

                $workflowitem = $usersequence->getWorkflowItem();

                $idworkflow = $workflowitem->get("idworkflow");
                $newpos = $workflowitem->get("position") - 1;

                if ($newpos < 1) {
                    $newpos = 1;
                }

                $workflowitems = new WorkflowItems;
                $workflowitems->select("idworkflow = '$idworkflow' AND position = '" . Contenido_Security::escapeDB($newpos, NULL) . "'");

                if ($nextObj = $workflowitems->next()) {
                    $userSequences = new WorkflowUserSequences;
                    $idworkflowitem = $nextObj->get("idworkflowitem");
                    $userSequences->select("idworkflowitem = '$idworkflowitem'");

                    if ($nextSeqObj = $userSequences->next()) {
                        $obj->set("lastusersequence", $obj->get("idusersequence"));
                        $obj->set("idusersequence", $nextSeqObj->get("idusersequence"));
                        $obj->set("laststatus", "last");
                        $obj->store();
                    }
                }
            }
            break;
        case "next":
            $artAllocations = new WorkflowArtAllocations;
            $artAllocations->select("idartlang = '$idartlang'");

            if ($obj = $artAllocations->next()) {
                $usersequence = new WorkflowUserSequence;
                $usersequence->loadByPrimaryKey($obj->get("idusersequence"));

                $workflowitem = $usersequence->getWorkflowItem();

                $idworkflow = $workflowitem->get("idworkflow");
                $newpos = $workflowitem->get("position") + 1;

                $workflowitems = new WorkflowItems;
                $workflowitems->select("idworkflow = '$idworkflow' AND position = '" . Contenido_Security::escapeDB($newpos, NULL) . "'");

                if ($nextObj = $workflowitems->next()) {
                    $userSequences = new WorkflowUserSequences;
                    $idworkflowitem = $nextObj->get("idworkflowitem");
                    $userSequences->select("idworkflowitem = '$idworkflowitem'");

                    if ($nextSeqObj = $userSequences->next()) {
                        $obj->set("lastusersequence", '10');
                        $obj->set("idusersequence", $nextSeqObj->get("idusersequence"));
                        $obj->set("laststatus", "confirm");
                        $obj->store();
                    }
                } else {
                    $workflowitems->select("idworkflow = '$idworkflow' AND position = '" . Contenido_Security::escapeDB($workflowitem->get("position"), NULL) . "'");
                    if ($nextObj = $workflowitems->next()) {
                        $userSequences = new WorkflowUserSequences;
                        $idworkflowitem = $nextObj->get("idworkflowitem");
                        $userSequences->select("idworkflowitem = '$idworkflowitem'");

                        if ($nextSeqObj = $userSequences->next()) {
                            $obj->set("lastusersequence", $obj->get("idusersequence"));
                            $obj->set("idusersequence", $nextSeqObj->get("idusersequence"));
                            $obj->set("laststatus", "confirm");
                            $obj->store();
                        }
                    }
                }
            }
            break;
        case "reject":
            $artAllocations = new WorkflowArtAllocations;
            $artAllocations->select("idartlang = '$idartlang'");

            if ($obj = $artAllocations->next()) {
                $usersequence = new WorkflowUserSequence;
                $usersequence->loadByPrimaryKey($obj->get("idusersequence"));

                $workflowitem = $usersequence->getWorkflowItem();

                $idworkflow = $workflowitem->get("idworkflow");
                $newpos = 1;

                $workflowitems = new WorkflowItems;
                $workflowitems->select("idworkflow = '$idworkflow' AND position = '" . Contenido_Security::escapeDB($newpos, NULL) . "'");

                if ($nextObj = $workflowitems->next()) {
                    $userSequences = new WorkflowUserSequences;
                    $idworkflowitem = $nextObj->get("idworkflowitem");
                    $userSequences->select("idworkflowitem = '$idworkflowitem'");

                    if ($nextSeqObj = $userSequences->next()) {
                        $obj->set("lastusersequence", $obj->get("idusersequence"));
                        $obj->set("idusersequence", $nextSeqObj->get("idusersequence"));
                        $obj->set("laststatus", "reject");
                        $obj->store();
                    }
                }
            }
            break;

        case "revise":
            $db = new DB_ConLite;
            $sql = "SELECT idart, idlang FROM " . $cfg["tab"]["art_lang"] . " WHERE idartlang = '" . Contenido_Security::escapeDB($idartlang, $db) . "'";
            $db->query($sql);
            $db->next_record();
            $idart = $db->f("idart");
            $idlang = $db->f("idlang");

            $newidart = conCopyArticle($idart, $idcat, "foo");

            break;
        default:
    }
}

function getWorkflowForUserSequence($usersequence) {
    $usersequences = new WorkflowUserSequences;
    $workflowitems = new WorkflowItems;
    $usersequences->select("idusersequence = '$usersequence'");

    if ($obj = $usersequences->next()) {
        $idworkflowitem = $obj->get("idworkflowitem");
    } else {
        return false;
    }

    $workflowitems->select("idworkflowitem = '$idworkflowitem'");
    if ($obj = $workflowitems->next()) {
        return $obj->get("idworkflow");
    } else {
        return false;
    }
}

function workflowSelect($listid, $default, $idcat) {
    global $idclient, $cfg, $frame, $area, $workflowworkflows, $client, $lang, $wfcache, $workflowSelectBox;

    $oSelectBox = new cHTMLSelectElement('workflow');
    $oSelectBox = $workflowSelectBox;

    $default = (int) $default;
    $workflowSelectBox->updateAttributes(array("id" => "wfselect" . $idcat));
    $workflowSelectBox->updateAttributes(array("name" => "wfselect" . $idcat));
    $workflowSelectBox->setDefault($default);

    $sButton = '<a href="javascript:setWorkflow(' . $idcat . ', \'' . "wfselect" . $idcat . '\')"><img src="' . $cfg["path"]["images"] . 'submit.gif" class="spaced"></a>';

    return $workflowSelectBox->render() . $sButton;
}

function workflowInherit($idcat) {
    global $idclient, $cfg, $frame, $area, $workflowworkflows, $sess;
    $sUrl = $sess->url("main.php?area=$area&frame=$frame&modidcat=$idcat&action=workflow_inherit_down");
    $sButton = '<a href="' . $sUrl . '"><img src="' . $cfg["path"]["images"] . 'pfeil_runter.gif" class="spaced"></a>';
    return $sButton;
}

/* Helper functions */

function getWorkflowForCat($idcat) {
    global $lang, $cfg;

    $idcatlang = getCatLang($idcat, $lang);
    $workflows = new WorkflowAllocations;
    $workflows->select("idcatlang = '$idcatlang'");
    if ($obj = $workflows->next()) {
        /* Sanity: Check if the workflow still exists */
        $workflow = new Workflow;

        $res = $workflow->loadByPrimaryKey($obj->get("idworkflow"));

        if ($res == false) {
            return 0;
        } else {
            return $obj->get("idworkflow");
        }
    }
}

function getCatLang($idcat, $idlang) {
    global $lang, $cfg;
    $db = new DB_ConLite;

    /* Get the idcatlang */
    $sql = "SELECT idcatlang FROM "
            . $cfg["tab"]["cat_lang"] .
            " WHERE idlang = '" . Contenido_Security::escapeDB($idlang, $db) . "' AND
             idcat = '" . Contenido_Security::escapeDB($idcat, $db) . "'";

    $db->query($sql);

    if ($db->next_record()) {
        return ($db->f("idcatlang"));
    }
}

function prepareWorkflowItems() {

    global $action, $lang, $modidcat, $workflowSelectBox, $workflowworkflows, $client, $tpl, $cfg;

    $workflowworkflows = new Workflows;

    if ($action === 'workflow_inherit_down') {
        $tmp = strDeeperCategoriesArray($modidcat);
        $asworkflow = getWorkflowForCat($modidcat);

        $wfa = new WorkflowAllocations;

        foreach ($tmp as $tmp_cat) {
            $idcatlang = getCatLang($tmp_cat, $lang);

            if ($asworkflow == 0) {
                $wfa->select("idcatlang = '$idcatlang'");

                if ($item = $wfa->next()) {
                    $wfa->delete($item->get("idallocation"));
                    # delete user sequences for listing in tasklist for each included article
                    $oArticles = new ArticleCollection(array('idcat' => $idcatlang, 'start' => true, 'offline' => true));
                    while ($oArticle = $oArticles->nextArticle()) {
                        setUserSequence($oArticle->getField('idartlang'), -1);
                    }
                }
            } else {
                $wfa->select("idcatlang = '$idcatlang'");

                if ($item = $wfa->next()) {
                    $item->setWorkflow($asworkflow);
                    $item->store();
                } else {
                    $wfa->create($asworkflow, $idcatlang);
                    # generate user sequences for listing in tasklist for each included article
                    $oArticles = new ArticleCollection(array('idcat' => $tmp_cat, 'start' => true, 'offline' => true));
                    while ($oArticle = $oArticles->nextArticle()) {
                        setUserSequence($oArticle->getField('idartlang'), $asworkflow);
                    }
                }
            }
        }
    }
    if ($action == "workflow_cat_assign") {
        $seltpl = "wfselect" . $modidcat;

        $wfa = new WorkflowAllocations;
        $idcatlang = getCatLang($modidcat, $lang);

        #associate workflow with category
        if ($GLOBALS[$seltpl] != 0) {
            $wfa->select("idcatlang = '$idcatlang'");
            if ($item = $wfa->next()) {
                $item->setWorkflow($GLOBALS[$seltpl]);
                $item->store();
            } else {
                $wfa->create($GLOBALS[$seltpl], $idcatlang);
            }

            # generate user sequences for listing in tasklist for each included article
            $oArticles = new ArticleCollection(array('idcat' => $modidcat, 'start' => true, 'offline' => true));
            while ($oArticle = $oArticles->nextArticle()) {
                setUserSequence($oArticle->getField('idartlang'), $GLOBALS[$seltpl]);
            }
            #unlink workflow with category
        } else {
            $wfa->select("idcatlang = '$idcatlang'");

            if ($item = $wfa->next()) {
                $alloc = $item->get("idallocation");
            }
            $wfa->delete($alloc);

            # delete user sequences for listing in tasklist for each included article
            $oArticles = new ArticleCollection(array('idcat' => $modidcat, 'start' => true, 'offline' => true));
            while ($oArticle = $oArticles->nextArticle()) {
                setUserSequence($oArticle->getField('idartlang'), -1);
            }
        }
    }

    $workflowSelectBox = new cHTMLSelectElement("foo");
    $workflowSelectBox->setClass("text_medium");
    $workflowworkflows->select("idclient = '$client' AND idlang = '" . Contenido_Security::escapeDB($lang, null) . "'");

    $workflowOption = new cHTMLOptionElement("--- " . i18n("None", "workflow") . " ---", 0);
    $workflowSelectBox->addOptionElement(0, $workflowOption);

    while ($workflow = $workflowworkflows->next()) {
        $workflowOption = new cHTMLOptionElement($workflow->get("name"), $workflow->get("idworkflow"));
        $workflowSelectBox->addOptionElement($workflow->get("idworkflow"), $workflowOption);
    }

    $workflowSelectBox->updateAttributes(array("id" => "wfselect{IDCAT}"));
    $workflowSelectBox->updateAttributes(array("name" => "wfselect{IDCAT}"));

    $tpl->set('s', 'PLUGIN_WORKFLOW', $workflowSelectBox->render() . '<a href="javascript:setWorkflow({IDCAT}, \\\'wfselect{IDCAT}\\\')"><img src="' . $cfg["path"]["images"] . 'submit.gif" class="spaced"></a>');
    $tpl->set('s', 'PLUGIN_WORKFLOW_TRANSLATION', i18n("Inherit workflow down", "workflow"));
}

function piworkflowCategoryRenderColumn($idcat, $type) {

    switch ($type) {
        case "workflow":
            $value = workflowInherit($idcat) . '<script type="text/javascript" id="wf' . $idcat . '">printWorkflowSelect(' . $idcat . ', ' . (int) getWorkflowForCat($idcat) . ');</script>';
            break;
    }

    return ($value);
}

function piworkflowCategoryColumns($array) {
    prepareWorkflowItems();
    $myarray = array("workflow" => i18n("Workflow", "workflow"));

    return ($myarray);
}

function piworkflowProcessActions($array) {
    global $idcat;
    $defaultidworkflow = getWorkflowForCat($idcat);

    if ($defaultidworkflow != 0) {
        $narray = array("todo",
            "wfartconf",
            "wftplconf",
            "wfonline",
            "wflocked",
            "duplicate",
            "delete",
            "usetime");
    } else {
        $narray = $array;
    }

    return ($narray);
}

function piworkflowRenderAction($idcat, $idart, $idartlang, $type) {
    global $area, $frame, $idtpl, $cfg, $alttitle, $tmp_articletitle;
    global $tmp_artconf, $onlinelink, $lockedlink, $tplconf_link;

    $defaultidworkflow = getWorkflowForCat($idcat);

    $idusersequence = getCurrentUserSequence($idartlang, $defaultidworkflow);
    $associatedUserSequence = new WorkflowUserSequence;
    $associatedUserSequence->loadByPrimaryKey($idusersequence);

    $currentEditor = $associatedUserSequence->get("iduser");
    $workflowItem = $associatedUserSequence->getWorkflowItem();

    if (isCurrentEditor($associatedUserSequence->get("iduser"))) {
        /* Query rights for this user */
        $wfRights = $workflowItem->getStepRights();
        $mayEdit = true;
    } else {
        $wfRights = "";
        $mayEdit = false;
    }

    switch ($type) {
        case "wfartconf":
            if ($wfRights["propertyedit"] == true) {
                return $tmp_artconf;
            }
            break;
        case "wfonline":
            if ($wfRights["publish"] == true) {
                return $onlinelink;
            }
            break;
        case "wflocked":
            if ($wfRights["lock"] == true) {
                return $lockedlink;
            }
            break;
        case "wftplconf":
            if ($wfRights["templateedit"] == true) {
                return $tplconf_link;
            }
        default:
            break;
    }

    return "";
}

function piworkflowProcessArticleColumns($array) {
    global $idcat, $action, $modidartlang;

    if ($action == "workflow_do_action") {
        $selectedAction = "wfselect" . $modidartlang;
        doWorkflowAction($modidartlang, $GLOBALS[$selectedAction]);
    }

    $defaultidworkflow = getWorkflowForCat($idcat);

    if ($defaultidworkflow != 0) {
        $narray = array();
        $bInserted = false;
        foreach ($array as $sKey => $sValue) {
            $narray[$sKey] = $sValue;
            if ($sKey == 'title' && !$bInserted) {
                $narray["wftitle"] = $array["title"];
                $narray["wfstep"] = i18n("Workflow Step", "cl-workflow");
                $narray["wfaction"] = i18n("Workflow Action", "cl-workflow");
                $narray["wfeditor"] = i18n("Workflow Editor", "cl-workflow");
                $narray["wflaststatus"] = i18n("Last status", "cl-workflow");
                $bInserted = true;
            }
        }
        unset($narray['title']);
        unset($narray['changeddate']);
        unset($narray['publisheddate']);
        unset($narray['sortorder']);
    } else {
        $narray = $array;
    }

    return ($narray);
}

function piworkflowAllowArticleEdit($idlang, $idcat, $idart, $user) {
    $defaultidworkflow = getWorkflowForCat($idcat);

    if ($defaultidworkflow == 0) {
        return true;
    }

    $idartlang = getArtLang($idart, $idlang);
    $idusersequence = getCurrentUserSequence($idartlang, $defaultidworkflow);
    $associatedUserSequence = new WorkflowUserSequence;
    $associatedUserSequence->loadByPrimaryKey($idusersequence);

    $currentEditor = $associatedUserSequence->get("iduser");

    $workflowItem = $associatedUserSequence->getWorkflowItem();

    if (isCurrentEditor($associatedUserSequence->get("iduser"))) {
        $wfRights = $workflowItem->getStepRights();
        $mayEdit = true;
    } else {
        $wfRights = "";
        $mayEdit = false;
    }

    if ($wfRights["articleedit"] == true) {
        return true;
    } else {
        return false;
    }
}

function piworkflowRenderColumn($idcat, $idart, $idartlang, $column) {
    global $area, $frame, $idtpl, $cfg, $alttitle, $tmp_articletitle;
    $defaultidworkflow = getWorkflowForCat($idcat);

    $idusersequence = getCurrentUserSequence($idartlang, $defaultidworkflow);
    $associatedUserSequence = new WorkflowUserSequence;
    $associatedUserSequence->loadByPrimaryKey($idusersequence);

    $currentEditor = $associatedUserSequence->get("iduser");

    $workflowItem = $associatedUserSequence->getWorkflowItem();

    if (isCurrentEditor($associatedUserSequence->get("iduser"))) {
        $wfRights = $workflowItem->getStepRights();
        $mayEdit = true;
    } else {
        $wfRights = "";
        $mayEdit = false;
    }

    switch ($column) {
        case "wftitle":
            if ($wfRights["articleedit"] == true) {
                $mtitle = $tmp_articletitle;
            } else {
                $mtitle = strip_tags($tmp_articletitle);
            }
            return ($mtitle);
        case "wfstep":
            if ($workflowItem === false) {
                return "nobody";
            }

            return ($workflowItem->get("position") . ".) " . $workflowItem->get("name"));
        case "wfeditor":
            $sEditor = getGroupOrUserName($currentEditor);
            if (!$sEditor) {
                $sEditor = "nobody";
            }
            return $sEditor;
        case "wfaction":
            $defaultidworkflow = getWorkflowForCat($idcat);
            $idusersequence = getCurrentUserSequence($idartlang, $defaultidworkflow);

            $sActionSelect = getActionSelect($idartlang, $idusersequence);
            if (!$sActionSelect) {
                $mayEdit = false;
            }

            $form = new UI_Form("wfaction" . $idartlang, "main.php", "get");
            $form->setVar("area", $area);
            $form->setVar("action", "workflow_do_action");
            $form->setVar("frame", $frame);
            $form->setVar("idcat", $idcat);
            $form->setVar("modidartlang", $idartlang);
            $form->setVar("idtpl", $idtpl);
            $form->add("select", '<table cellspacing="0" border="0"><tr><td>' . $sActionSelect . '</td><td>');
            $form->add("button", '<input type="image" src="' . $cfg["path"]["htmlpath"] . $cfg["path"]["images"] . "submit.gif" . '"></tr></table>');

            if ($mayEdit == true) {
                return ($form->render(true));
            } else {
                return '--- ' . i18n("None") . ' ---';
            }

        case "wflaststatus":
            $sStatus = getLastWorkflowStatus($idartlang);
            if (!$sStatus) {
                $sStatus = '--- ' . i18n("None") . ' ---';
            }
            return $sStatus;
    }
}

function piworkflowCreateTasksFolder() {
    global $sess, $cfg;

    $item = array();
    /* Create workflow tasks folder */
    $tmp_mstr = '<a href="javascript://" onclick="javascript:conMultiLink(\'%s\', \'%s\', \'%s\', \'%s\')">%s</a>';

    $mstr = sprintf($tmp_mstr, 'right_bottom',
            $sess->url("main.php?area=con_workflow&frame=4"),
            'right_top',
            $sess->url("main.php?area=con_workflow&frame=3"),
            'Workflow / Todo');

    $item["image"] = '<img src="' . $cfg["path"]["contenido_fullhtml"] . $cfg["path"]["plugins"] . 'workflow/images/workflow_erstellen.gif">';
    $item["title"] = $mstr;


    return ($item);
}