<?php
$sAutoloadClassPath = strstr(dirname(dirname(__FILE__)), "conlite/plugins")."/classes/";
return array(
    'Workflow' => $sAutoloadClassPath.'class.workflow.php',
    'Workflows' => $sAutoloadClassPath.'class.workflow.php',
    'WorkflowAction' => $sAutoloadClassPath.'class.workflowactions.php',
    'WorkflowActions' => $sAutoloadClassPath.'class.workflowactions.php',
    'WorkflowAllocation' => $sAutoloadClassPath.'class.workflowallocation.php',
    'WorkflowAllocations' => $sAutoloadClassPath.'class.workflowallocation.php',
    'WorkflowArtAllocation' => $sAutoloadClassPath.'class.workflowartallocation.php',
    'WorkflowArtAllocations' => $sAutoloadClassPath.'class.workflowartallocation.php',
    'WorkflowItem' => $sAutoloadClassPath.'class.workflowitems.php',
    'WorkflowItems' => $sAutoloadClassPath.'class.workflowitems.php',
    'WorkflowTask' => $sAutoloadClassPath.'class.workflowtasks.php',
    'WorkflowTasks' => $sAutoloadClassPath.'class.workflowtasks.php',
    'WorkflowUserSequence' => $sAutoloadClassPath.'class.workflowusersequence.php',
    'WorkflowUserSequences' => $sAutoloadClassPath.'class.workflowusersequence.php'
);
?>