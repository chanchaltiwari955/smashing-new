<?php

namespace Drupal\employee\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Code\Database\Database;

class EmployeeController extends ControllerBase{
     public function detailsEmployee(){
          $form = \Drupal::formBuilder()->getForm('Drupal\employee\Form\EmployeeForm');
          // $renderForm = \Drupal::service('renderer')->render($form);

          return [
               '#theme'=>'employee',
               '#items'=>$form,
               '#title'=>'employee Form'
          ];
     }

     public function getEmployeeList(){
          $limit = 3;
          $query = \Drupal::database();
          $result = $query->select('employee','e')
                  ->fields('e',['id','name','gender','about_employee'])
                  ->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit($limit)
                  ->execute()->fetchAll(\PDO::FETCH_OBJ);

          $data = [];
          $count = 0;
          $params = \Drupal::request()->query->all();
          if(empty($params) || $params['page'] == 0){
              $count=1;
          }elseif($params['page']==1){
              $count = $params['page'] + $limit;
          }else{
              $count = $params['page'] * $limit;
              $count++;
          }
          foreach($result as $row){
              $data[] = [
                  'Serial_no'=>$count.".",
                  'name' => $row->name,
                  'gender' => $row->gender, 
                  'about_employee' => $row->about_employee,
                  'edit'=>t("<a href='edit-employee/$row->id'>Edit</a>"),
                  'Delete'=>t("<a href='delete-employee/$row->id'>Delete</a>")
              ];
              $count++;
          }
          $header = array('Id','Name','Gender','About Employee','Edit','Delete');
          $build['table']=[
              '#type'=>'table',
              '#class'=>'table',
              '#header'=>$header,
              '#rows'=>$data
          ];
          $build['pager'] = ['#type'=>'pager'];
          return [
              $build,
              '#title' => 'Employee List',
          ];
      }

        public function deleteEmployee($id){
            $query = \Drupal::database();
            $query->delete('employee')
            ->condition('id',$id,'=')
            ->execute();

        $response = new \Symfony\Component\HttpFoundation\RedirectResponse('../employee-list');
        $response->send();

        \Drupal::messenger()->addMessage($this->t('Form Deleted Successfully'), 'status', TRUE);
        }
}