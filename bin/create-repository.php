<?php
chdir(dirname(__FILE__));
require_once dirname(dirname(__FILE__)). '/__init__.php';
Rhaco::import('single_execution');
Rhaco::import('Package');
Rhaco::import('model.NewprojectQueue');

new singleExecution();

$db = new DbUtil(NewprojectQueue::connection());
$queue = $db->get(new NewprojectQueue(), new C(Q::order(NewprojectQueue::columnCreated())));

if(Variable::istype('NewprojectQueue', $queue) && $package = $db->get(new Package($queue->package))){
    SvnUtil::import(
        Rhaco::resource('project_base'),
        sprintf('file://%s/%s', Rhaco::constant('SVN_PATH'), $package->name),
        'message=[Add Package] '. $package->name
    );
    if($queue->isMailPossible()){
        // send welcome mail!
    }
}
