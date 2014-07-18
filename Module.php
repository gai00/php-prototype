<?php
namespace {
    class Module extends Prototype {
        protected $parent = null;
        
        public static $funcSetModule;
        public static $funcListModule;
        public static $funcHoist;
        
        public static function enhance($prototype) {
            $prototype->setFunc('_setModule', self::$funcSetModule);
            $prototype->setFunc('_listModule', self::$funcListModule);
            $prototype->setFunc('_hoist', self::$funcHoist);
        }
        
        protected function moduleInit(Prototype $prototype, $name) {
            $this->setParent($prototype);
            $prototype->setComponent($name, $this);
        }
        
        protected function getParent() {
            return $this->parent;
        }
        
        protected function setParent($parent) {
            $this->parent = $parent;
        }
    }
    
    Module::$funcSetModule = function($that, $name, $module = null) {
        if(is_array($name)) {
            foreach($name as $varName => $varMod) {
                $varMod->moduleInit($that, $varName);
            }
        } elseif(is_string($name)) {
            $module->moduleInit($that, $name);
        } else {
            throw new Exception('Argument 1 must be a string or array pair.');
        }
    };
    
    Module::$funcListModule = function($that) {
        return array_keys($that->getComponent(true));
    };
    
    Module::$funcHoist = function($that, $moduleName, $funcName) {
        $varModule = $that;
        if(is_array($moduleName)) {
            foreach($moduleName as $varName) {
                $varModule = $varModule->$varName;
            }
        } elseif(is_object($moduleName)) {
            $varModule = $moduleName;
        } elseif(is_string($moduleName)) {
            $varModule = $varModule->$moduleName;
        }
        $that->setFunc($funcName, array($varModule, $funcName));
    };
}
?>
