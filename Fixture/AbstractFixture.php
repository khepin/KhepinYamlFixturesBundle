<?php

namespace Khepin\YamlFixturesBundle\Fixture;

use Symfony\Component\Yaml\Yaml;

class AbstractFixture {

    protected $tags = array();
    
    protected $file;
    
    protected $loader;
    
    public function __construct(array $data, $loader) {
        $this->file = $data;
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