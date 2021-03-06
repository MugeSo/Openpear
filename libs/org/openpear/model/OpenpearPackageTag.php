<?php
import('org.rhaco.storage.db.Dao');

/**
 * Package Tag
 *
 * @var integer $package_id @{"require":true,"primary":true}
 * @var integer $tag_id @{"require":true,"primary":true}
 * @var boolean $prime
 */
class OpenpearPackageTag extends Dao
{
    protected $package_id;
    protected $tag_id;
    protected $prime;
    
    private $package;
    private $tag;
    
    protected function __init__(){
        $this->prime = false;
    }
    protected function __after_save__(){
        $this->_reset_primaries();
        $this->_recount_tags();
        if($this->prime() === true){
            try {
                if(C(OpenpearPackageTag)->find_count(Q::eq('prime', true), Q::eq('package_id', $this->package_id())) > 0){
                    foreach(C(OpenpearPackageTag)->find_all(Q::eq('package_id', $this->package_id())) as $package_tag){
                        if($package_tag->tag_id() !== $this->tag_id() && $package_tag->prime() === true){
                            $package_tag->prime(false);
                            $package_tag->save();
                        }
                    }
                }
            } catch(Exception $e){}
        }
    }
    public function after_delete(){
        $this->_reset_primaries();
        $this->_recount_tags();
        if(C(OpenpearPackageTag)->find_count(Q::neq('package_id', $this->package_id()), Q::eq('tag_id', $this->tag_id())) === 0){
            $tag = $this->tag();
            $tag->delete();
        }
    }
    private function _recount_tags(){
        try {
            $tag = $this->tag();
            $tag->package_count(C(OpenpearPackageTag)->find_count(Q::eq('tag_id', $this->tag_id())));
            $tag->save();
        } catch (Exception $e){}
    }
    private function _reset_primaries(){
        try {
            $tag = C(OpenpearTag)->find_get(Q::eq('id', $this->tag_id()));
            if($this->prime() === true && $tag->prime() === true){
                return ;
            } else if($this->prime() === true && $tag->prime() === false){
                $tag->prime(true);
                $tag->save();
                return ;
            }
            switch($tag->prime()){
                case true:
                    if(C(OpenpearPackageTag)->find_count(Q::eq('prime', true), Q::eq('tag_id', $this->tag_id())) === 0){
                        $tag->prime(false);
                        $tag->save();
                    }
                    break;
                case false:
                    if(C(OpenpearPackageTag)->find_count(Q::eq('prime', true), Q::eq('tag_id', $this->tag_id())) > 0){
                        $tag->prime(true);
                        $tag->save();
                    }
                    break;
            }
        } catch(Exception $e){}
    }
    
    public function package(){
        if($this->package instanceof OpenpearPackage){
            return $this->package;
        }
        try{
            $this->package = C(OpenpearPackage)->find_get(Q::eq('id', $this->package_id()));
        }catch(Exception $e){}
        return $this->package;
    }
    public function tag(){
        if($this->tag instanceof OpenpearTag){
            return $this->tag;
        }
        try{
            $this->tag = C(OpenpearTag)->find_get(Q::eq('id', $this->tag_id()));
        }catch(Exception $e){}
        return $this->tag;
    }
}
