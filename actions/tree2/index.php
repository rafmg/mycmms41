<?php
/** 
* Tree representation of data
* 
* @author  Werner Huysmans 
* @access  public
* @package mycmms40
* @subpackage framework
* @filesource
*/
$nosecurity_check=true;
$deep2=true;
require_once("../../includes/config_mycmms.inc.php");
include_once('./treenode_class.php');

if (isset($_REQUEST['tree'])) {
    $_SESSION['tree']=$_REQUEST['tree'];    
}
/**
* Read treewindow.ini file
* 
* @var array
*/
$ini_array = parse_ini_file("treewindow.ini", true);
$table_width=$ini_array[$_SESSION['tree']]["table_width"];
$tree_table=$ini_array[$_SESSION['tree']]["tree_table"];
$table_title=$ini_array[$_SESSION['tree']]["table_title"];
$myfunction=$ini_array[$_SESSION['tree']]["function"];

/**
* Expand all lines, start by selecting all items with children
* @param mixed $expanded
*/
function expand_all(&$expanded) {
      global $tree_table;  
      $DB=DBC::get();
      $result=$DB->query("SELECT postid from $tree_table where children=1");
      foreach ($result->fetchAll(PDO::FETCH_NUM) as $row) {
            $expanded[$row[0]]=true;
      }
} // EO expand_all
/**
* Find parent from item and show its children (in case we call from a tabwindow)
* @param mixed $eqnum
*/
function make_array($eqnum) {
    global $tree_table;
    $DB=DBC::get();
    $parentId=DBC::fetchcolumn("SELECT parent FROM $tree_table WHERE EQNUM='$eqnum'",0);
    $node[$parentId]=1;
    while ($parentId != 0) {
        $result=$DB->query("SELECT parent FROM $tree_table WHERE postid=$parentId");
        $row=$result->fetch(PDO::FETCH_ASSOC);
        $parentId=$row['parent'];
        $node[$parentId]=1;
    }
    return $node;
} // EO make_array

if(!isset($_SESSION['expanded']))  {
        $_SESSION['expanded'] = array();
} # Check if we have created our session variable
if(isset($_GET['expand']))   {
    if($_GET['expand']=='all') {
        expand_all($_SESSION['expanded']);
    } else {
        $_SESSION['expanded'][$_GET['expand']] = true;
    }
} # Check if an expand button was pressed expand might equal 'all' or a postid or not be set
if(isset($_GET['collapse'])) {
    if($_GET['collapse']=='all') {
        $_SESSION['expanded'] = array();
    } else {
        unset($_SESSION['expanded'][$_GET['collapse']]);
    }
} # Check if a collapse button was pressed collapse might equal all or a postid or not be set
if(isset($_REQUEST['root'])) {
    $_SESSION['rooteqnum']=$_REQUEST['root'];   
} # root
if(isset($_GET['shownode'])) {
    $_SESSION['expanded']=make_array($_SESSION['rooteqnum']);
} # shownode
if(isset($_REQUEST['SET'])) {
    $DB=DBC::get();
    $rootnode=DBC::fetchcolumn("SELECT postid FROM $tree_table WHERE EQNUM='{$_REQUEST['ROOT']}'",0);
    $_SESSION['rooteqnum']=$rootnode;
} 
if(!isset($_SESSION['rooteqnum'])) {
    $_SESSION['rooteqnum']=0;
}
$labels=array(
    "TableWidth"=>$table_width,
    "TableTitle"=>_($table_title),
    "stylesheet"=>STYLE_PATH."/".CMMS_STYLESHEET."/tree.css",
    "stylesheet_type"=>STYLE_PATH."/".CMMS_STYLESHEET."/tree_keytype.css"
);
require("setup.php");

# Header
$tpl=new smarty_mycmms();
$tpl->caching=false;
$tpl->assign('labels',$labels);
$tpl->assign('rooteqnum',$_SESSION['rooteqnum']);
$tpl->assign('tree_table',$_SESSION['tree']);
$tpl->display("treewindow_header.tpl");
# Nodes
$treeleaf=new treenode($_SESSION['rooteqnum'],'','','','',1,true,-1,$_SESSION['expanded'],false);
$treeleaf->display($row);
# Footer
$tpl=new smarty_mycmms();
$tpl->caching=false;
$tpl->assign('labels',$labels);
$tpl->display("treewindow_footer.tpl");
?>
