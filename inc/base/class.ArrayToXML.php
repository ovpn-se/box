<?php
namespace Base;

class ArrayToXML {
    /** 
     * Parse multidimentional array to XML. 
     * 
     * @param array $array 
     * @return string    XML 
     */ 
    var $XMLtext; 
     
    public function array2xml($array, $output=true) { 
        //star and end the XML document 
        $this->XMLtext="<?xml version=\"1.0\">\n<pfsense>\n";
        $this->array_transform($array, 1); 
        $this->XMLtext .="</pfsense>";
        if($output) return $this->XMLtext; 
    }

    public function SaveXml($src){ 
        $myFile = ""; 
        $fh = file_put_contents($src, $this->XMLtext);
        if($fh){ 
            return true; 
        }else { 
            return false; 
        } 
         
    } 
    public function array_transform($array, $count){
        static $Depth; 
        
        $newCount = $count++;
        
        
        $z = 1;

        foreach($array as $key => $value){
            
            $z++;
            
            if(!is_array($value)){
                
                if(isset($Tabs)) {
                  unset($Tabs);   
                }
                 
                for($i=1;$i<=$Depth+1;$i++) { if(isset($Tabs)) { $Tabs .= "\t";} else { $Tabs = "\t" ;}  }
                if(preg_match("/^[0-9]{1,}$/",$key))  { $key = "n$key";}
                
                if(strpos($value, "<") !== FALSE || strpos($value, "&") !== FALSE  || strpos($value, ">") !== FALSE ) {
                    $this->XMLtext .= "$Tabs<$key><![CDATA[$value]]></$key>\n"; 
                } else {
                    $this->XMLtext .= "$Tabs<$key>$value</$key>\n"; 
                }
                
            } else { 
                $Depth += 1; 
                unset($Tabs); 
                for($i=1;$i<=$Depth;$i++) { if(isset($Tabs)) { $Tabs .= "\t";} else { $Tabs = "\t" ;}  }
                //search for atribut like [name]-ATTR to put atributs to some object 
                if(!preg_match("/(-ATTR)\$/", $key)) { 
                    if(preg_match("/^[0-9]{1,}$/",$key)) { $keyval = "n$key"; } else $keyval = $key; 
                    $closekey = $keyval; 
                    if(isset($array[$key."-ATTR"]) && is_array($array[$key."-ATTR"])){ 
                        foreach ($array[$key."-ATTR"] as $atrkey => $atrval ) $keyval .= " ".$atrkey."=\"$atrval\""; 
                    }  
                    $this->XMLtext.="$Tabs<$keyval>\n"; 
                    $this->array_transform($value, $newCount); 
                    $this->XMLtext.="$Tabs</$closekey>\n"; 
                    $Depth -= 1; 
                     
                } 
            } 
        } 
        return true; 
    } 
} 
?>