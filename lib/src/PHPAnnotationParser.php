<?php

/**
 * PHPAnnotationParser parses method and class annotation to be used as a metadata
 * @author flchaux
 */
class PHPAnnotationParser {
    /**
     *
     * @var ReflectionClass  
     */
    private $reflectiveDescription;
    /**
     *
     * @var array 
     */
    private $classAnnotation;
    /**
     *
     * @var array 
     */
    private $methodsAnnotation;
    
    /**
     * 
     * @param string $class class name
     * @return array an array of annotation
     */
    public function parseClassAnnotation($class){
        return $this->parseAnnotation($this->reflectiveDescription->getDocComment());
    }
    /**
     * 
     * @return array an array of annotation for all the methods
     */
    public function parseMethodsAnnotation(){
        $methods = $this->reflectiveDescription->getMethods();
        $annotations = array();
        foreach ($methods as $method){
            $annotations[$method->name] = $this->parseMethodAnnotation($method->name);
        }
        return $annotations;
    }
    /**
     * 
     * @param string $doc the entire comment before a method or a class
     * @return array an array containing instructions and comments
     */
    protected function parseAnnotation($doc){
        $doc = preg_replace('#\/\*\*(.*)\*\/#isU', '$1', trim($doc));
        $lines = preg_split("#\n#", $doc);
        $instructions = array();
        $comments = array();
        foreach($lines as $line){
            $line = trim(preg_replace('#^\*#', '', trim($line)));
            if(preg_match('#^\@.*$#', trim(preg_replace('#^\*#', '', $line)))){
                $instructions[] = $this->parseInstruction(trim($line));
            }
            elseif(trim($line) != ''){
                $comments[] = trim($line);
            }
        }
        return array('instructions' => $instructions, 'comments' => $comments);
    }
    
    protected function parseInstruction($instruction){
        $matches = array();
        $instruction = preg_replace('#^@#', '', $instruction);
        if(preg_match('#(\D*)\((.*)\)#', $instruction, $matches)){
            $args = preg_split('#,(?=([^\"]*\"[^\"]*\")*[^\"]*$)#is', $matches[2]);
            foreach($args as $i => $arg){
                $arg = trim($arg);
                $args[$i] = $arg;
            }
            return array('name' => trim($matches[1]), 'args' => $args);
        }
        else{
            $args = preg_split('# (?=([^\"]*\"[^\"]*\")*[^\"]*$)#is', $instruction);
            foreach($args as $i => $arg){
                $arg = trim($arg);
                $args[$i] = $arg;
            }
            $name = array_shift($args);
            return array('name' => $name, 'args' => $args);
        }
    }
    
    /**
     * 
     * @param string $methodName the method name
     * @return array 
     */
    public function parseMethodAnnotation($methodName){
        $doc = $this->reflectiveDescription->getMethod($methodName)->getDocComment();
        return $this->parseAnnotation($doc);
    }
    /**
     * 
     * @param string $class the class name
     */
    public function __construct($class) {
        $this->reflectiveDescription = new ReflectionClass($class);
        $this->classAnnotation = $this->parseClassAnnotation($class);
        $this->methodsAnnotation = $this->parseMethodsAnnotation();
    }
    
    public function getClassAnnotation() {
        return $this->classAnnotation;
    }

    public function getMethodsAnnotation() {
        return $this->methodsAnnotation;
    }
    public function getMethodAnnotation($methodName) {
        if(isset($this->methodsAnnotation[$methodName]))
            return $this->methodsAnnotation[$methodName];
        else
            throw new Exception ('Invalid method name ('.$methodName.')', 12, null);
    }
}
