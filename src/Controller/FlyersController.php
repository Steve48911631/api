<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Flyers Controller
 *
 * @method \App\Model\Entity\Flyer[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class FlyersController extends AppController
{
    
    public function initialize(): void
    {
        parent::initialize();
        
        $this->loadComponent('RequestHandler');
        
        /*
         * Can be added others columns to be filtered
         */
        
        $this->loadComponent('Flyer',[
            
            'file' => RESOURCES . DS . 'data.csv',
                        
            'allowed_filter' => [         
                'category',
                'is_published',               
            ],
            
            /*
             * Default filters applied to "find" action
             * allowed operands as follow:
             * ==
             * != 
             * >=
             * <=
             * >
             * <
             */
            'initial_filters'=>[
                'start_date'=>[
                    'operand'=>'<=',
                    'value'=>date('Y-m-d'),
                ],
                'end_date'=>[
                    'operand'=>'>=',
                    'value'=>date('Y-m-d'),
                ],
                
            ],
        ]);
      
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
      
        if($this->request->is('get')){
        
            //default GET params
            $page = (int)$this->request->getQuery('page');
            $limit = (int)$this->request->getQuery('limit');
      
            //optional GET params        
            $fields = $this->request->getQuery('fields');
            $filter = $this->request->getQuery('filter');
                           
            $response = $this->Flyer->find(null,$page,$limit,$fields,$filter);
    
        }else{
            
            $response = [
            'success' => false,
            'code' => 400,
            'error' => [
                    'message'=> 'Bad Request',
                    'debug' => 'Only GET method is allowed',
                ],
            ];
            
        }
        
        $this->set(compact('response'));
        $this->set('_serialize', 'response');
    }

    /**
     * View method
     *
     * @param string|null $id Flyer id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
            
        if($this->request->is('get')){
        
            $id = (int)$id;
           
            //optional GET params
            $fields = $this->request->getQuery('fields');
                    
            $response = $this->Flyer->findOne($id,$fields);
        
        }else{
            
            $response = [
                'success' => false,
                'code' => 400,
                'error' => [
                    'message'=> 'Bad Request',
                    'debug' => 'Only GET method is allowed',
                ],
            ];
            
        }
            
        $this->set(compact('response'));
        $this->set('_serialize', 'response');
        
    }
   
}
