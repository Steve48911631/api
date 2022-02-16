<?php 

namespace App\Controller\Component;

use Cake\Controller\Component;

class FlyerComponent extends Component
{
    
    /*
     * can be added more columns to be filtered
     */
    protected $allowed_filter;  
    protected $file;
    protected $initial_filters;
    
    protected $page;
    protected $limit;
    protected $fields;
    protected $filter;
    
    
    protected $columns;
      
    protected $response;
    
    public function initialize(array $config): void
    {
        $this->file = $config['file'];
        $this->allowed_filter = $config['allowed_filter'];
        $this->initial_filters = isset($config['initial_filters']) ? $config['initial_filters'] : [];
    }
    
    protected function _getColumns(){
        
        // open the file
        $fp = fopen($this->file, "r");
        
        $this->columns = fgetcsv($fp, 4096, ",");
        
        // close the file
        fclose($fp);
        
    }
    
    protected function _checkParams(){
        
        $this->page = $this->page > 0 ? $this->page : 1;
        $this->limit = $this->limit > 0 ? $this->limit : 100;
           
        /*
         * Field param is checked 
         * if field is not present in columns header return error
         */
       
        if(!empty($this->fields)){
            
            $fields = explode(',',$this->fields);
            
            $no_fields = array_diff($fields, $this->columns);
            
            if($no_fields){ //if field not exists return error
                
                $result = implode(',',$no_fields);
                $this->response = [
                    'success' => false,
                    'code' => 400,
                    'error' => [
                        'message'=> 'Bad Request',
                        'debug' => "Not allowed fields: $result",
                    ],
                ];  
                
                return;
            }
            
            //convert array fields into Key = value
            $this->fields = [];
            foreach($fields as $val)
                $this->fields[$val] = $val;
        }
        
        /*
         * Filter param is checked
         * allowed filters [
         *      category,
         *      is_published
         * ]
         */
      
        if(!empty($this->filter)){
              
            if(!is_array($this->filter)){
                
                $this->response = [
                    'success' => false,
                    'code' => 400,
                    'error' => [
                        'message'=> 'Bad Request',
                        'debug' => 'filter param mast be an array',
                    ],
                ];
                
                return;
                
            }
            
           
            $errors = [];
            foreach($this->filter as $filter => $val){
                if(!in_array($filter, $this->allowed_filter))
                    $errors[] = $filter;
            }
            
            if(!empty($errors)){
                
                $result = implode(',',$errors);
                $this->response = [
                    'success' => false,
                    'code' => 400,
                    'error' => [
                        'message'=> 'Bad Request',
                        'debug' => "Not allowed filters: $result",
                    ],
                ];
                
                return;
                
            }

        }
        
    }
    
    protected function _readData($id = null){
       
        // open the file
        $fp = fopen($this->file, "r");
        //remove first row that it's the header
        fgetcsv($fp, 4096, ",");
        
        $data = [];
          
        //setting pagination limits
        $start = ( ( $this->page * $this->limit ) - $this->limit ) + 1;
        $end = $this->page * $this->limit;
      
        // read each data row in the file
        $row = 1;        
        while (($line = fgetcsv($fp, 4096, ",")) !== FALSE) {
                     
            if(isset($id)){
                
                $row_id = (int)$line[array_search('id', $this->columns)];
                if($id == $row_id){
                    
                    $item = array_combine($this->columns, $line);
                    /*
                     * if fields params is not set print all columns
                     */
                    if(!empty($this->fields))
                        $data[] = array_intersect_key($item,$this->fields);
                    else
                        $data[] = $item;
                            
                    break;
                            
                }
            
            }else{
                                                              
                if($this->_auto_filters($line))
                    continue;
                
                /*
                 * Check filters
                 * 
                 * if all conditions of filters are not found, skip next row
                 * 
                 */
                if($this->_filters($line))
                    continue;
                
                       
                //pagination
                if($row >= $start){
                    
                    $item = array_combine($this->columns, $line);                                      
                    
                    /*
                     * if fields params is not set print all columns
                     */
                    if(!empty($this->fields))
                        $data[] = array_intersect_key($item,$this->fields);
                    else
                        $data[] = $item;
                  
                }
                
                if($row == $end) //limit of pagination reached
                    break;
                    
               
                $row++;
                
            }                     
        }
        
        // close the file
        fclose($fp);
        
        
        if(empty($data)){
            
            $this->response = [
                'success' => false,
                'code' => 404,
                'error' => [
                    'message'=> 'Not found',
                    'debug' => isset($id) ? "Resource $id not found" : 'Not found',
                ],
            ];
            
        }else{
            
            $this->response = [
                'success' => true,
                'code' => 200,
                'results' => $data,
            ];
            
        }
    
    }
    
    protected function _auto_filters($line){
        
        $skip = false;
        
        $and_operations = [];
        foreach($this->initial_filters as $col => $operation){
            
            $index = array_search($col, $this->columns);
            if($index !== false && isset($operation['operand']) && isset($operation['value'])){
                
                $value = $operation['value'];
                $cell_val = $line[$index];
                
                switch($operation['operand']){
                    
                    case '==':
                        $and_operations[] = ($cell_val == $value) ? true : false;
                    break;
                    case '!=':
                        $and_operations[] = ($cell_val != $value) ? true : false;
                    break;
                    case '>=':
                        $and_operations[] = ($cell_val >= $value) ? true : false;
                    break;
                    case '<=':
                        $and_operations[] = ($cell_val <= $value) ? true : false;
                    break;
                    case '>':
                        $and_operations[] = ($cell_val > $value) ? true : false;
                    break;
                    case '<':
                        $and_operations[] = ($cell_val < $value) ? true : false;
                    break;
                        
                }    
            }
        }
    
        if(!empty($and_operations) && in_array(false, $and_operations))
            $skip = true;
        
        return $skip;
    }
     
    protected function _filters($line){
        
        $not_found = false;
        foreach($this->filter as $col => $val){
            
            $key = array_search($col, $this->columns);
            if(isset($line[$key])){
                
                $field = strtolower($line[$key]);
                
                if($field != strtolower($val)){ 
                    $not_found = true;
                    break;                   
                }
                
            } 
        }
        
        return $not_found;
        
    }
    
    public function find($id = null,$page = 0,$limit = 0,$fields = null,$filter = [])
    {
       
        //initialize params 
        $this->page = $page;
        $this->limit = $limit;
        $this->fields = $fields;       
        $this->filter = isset($filter) ? $filter : [];
             
        $this->_getColumns();
        
        $this->_checkParams();
         
        if(!$this->response)
            $this->_readData($id);
     
        return $this->_response();
        
    }
    
    public function findOne($id,$fields = null)
    {
       
        return $this->find($id,0,0,$fields);
            
    }
    
    protected function _response(){
        
        return $this->response;
        
    }
    
}