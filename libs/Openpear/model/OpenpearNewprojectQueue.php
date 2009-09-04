<?php
import('org.rhaco.storage.db.Dao');

class OpenpearNewprojectQueue extends Dao
{
    protected $_database_ = 'openpear';
    protected $_table_ = 'newproject_queue';
    
    protected $id;
    protected $package_id;
    protected $maintainer_id;
    protected $mail_possible = true;
    protected $settings;
    protected $trial_count = 0;
    protected $created;
    
    static protected $__id__ = 'type=serial';
    static protected $__package_id__ = 'type=number,require=true';
    static protected $__maintainer_id__ = 'type=number,require=true';
    static protected $__mail_possible__ = 'type=boolean';
    static protected $__settings__ = 'type=text';
    static protected $__trial_count__ = 'type=number';
    static protected $__created__ = 'type=timestamp';
    
    protected $package;
    protected $maintainer;
    static protected $__package__ = 'type=OpenpearPackage,extra=true';
    static protected $__maintainer__ = 'type=OpenpearMaintainer,extra=true';
    
    public function __init__(){
        $this->trial_count = 0;
        $this->created = time();
    }
    public function create(){
        try {
            Subversion::cmd('import', array(
                path('resources/skelton'),
                File::absolute(def('svn_root'), $this->package()->name()),
            ),array(
                'message' => '[New Package] '. $this->package()->name(),
            ));
            // $message = new OpenpearMessage();
            // $message->subject('New Package is ready for your commit!');
            $this->delete();
            C($this)->commit();
        } catch (Exception $e){
            Log::error($e->getMessage());
            $this->trial_count += 1;
            $this->save();
            C($this)->commit();
        }
    }
    protected function getPackage(){
        if(is_object($this->package)) return $this->package;
        $this->package = C(OpenpearPackage)->find_get(Q::eq('id', $this->package_id()));
        return $this->package;
    }
    protected function getMaintainer(){
        if(is_object($this->maintainer)) return $this->maintainer;
        $this->maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->maintainer_id()));
        return $this->maintainer;
    }
}