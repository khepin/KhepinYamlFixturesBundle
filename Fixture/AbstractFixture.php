<?php

namespace Khepin\YamlFixturesBundle\Fixture;

class AbstractFixture {

    private $tags = array();
    
    private $file;
    
    private $loader;
    
    public function __construct($file, $loader) {
        $this->file = Yaml::parse($file);
        if(isset($this->file['tags'])){
            $this->tags = $this->file['tags'];
        }
        $this->loader = $loader;
    }
    

    /**
     * Returns if the given tag is set for the current fixture
     * @param type $tag
     * @return boolean 
     */
    public function hasTag(Array $tags){
        // if no tags were specified, the fixture should always be loaded
        if(count($this->tags) == 0 || count(array_intersect($this->tags, $tags)) > 0 ){
            return true;
        }
        return false;
    }
}