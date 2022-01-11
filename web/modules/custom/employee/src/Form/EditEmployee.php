<?php
     namespace Drupal\employee\Form;
     use Drupal\Core\Form\FormBase;
     use Drupal\Core\Form\FormStateInterface;
     use Drupal\Code\Database\Database;

     class EditEmployee extends FormBase{
          /**
           * {@inheritdoc}
           */

           public function getFormId() {
                return 'edit_employee';
           }

           /**
            * {@inheritdoc}
            */

            public function buildForm(array $form, FormStateInterface $form_state){

               $id = \Drupal::routeMatch()->getParameter('id');
               $query = \Drupal::database();
               $data = $query->select('employee','e')
                      ->fields('e',['id','name','gender','about_employee'])
                      ->condition('e.id',$id,'=')
                      ->execute()->fetchAll(\PDO::FETCH_OBJ);
                      
               // print_r($data);

               $genderOptions = array(
                    '0'=>'Select Gender',
                    'Male' =>'Male',
                    'Female'=>'Female',
                    'Other'=>'Other'
               );

               $form['name'] = array(
                    '#type' =>'textfield',
                    '#title'=>t('Name'),
                    '#default_value'=>$data[0]->name,
                    '#required' => true
               );

               $form['gender'] = array(
                    '#type' =>'select',
                    '#title'=>'Gender',
                    '#options'=>$genderOptions,
                    '#default_value'=>$data[0]->gender,
                    '#required' => true
               );

               $form['about_employee'] = array(
                    '#type' =>'textarea',
                    '#title'=>'About Employee',
                    '#default_value'=>$data[0]->about_employee,
                    '#required' => true
               );

               $form['update'] = array(
                    '#type' =>'submit',
                    '#title'=>'update Employee',
                    '#button_type'=>'primary'
               );

               return $form;
                    
            }

            /**
             * {@inheritdoc}
             */

             public function validateForm(array &$form, FormStateInterface $form_state){

               $name = $form_state->getValue('name');
               if(trim($name) == ''){
                    $form_state->setErrorByName('name',$this->t('Name field is required'));
               }
               if($form_state->getValue('gender') == '0'){
                    $form_state->setErrorByName('gender', $this->t('gender field is required'));
               }
               if($form_state->getValue('about_employee') == ''){
                    $form_state->setErrorByName('about_employee', $this->t('about employee field is required'));
               }

                
             }

            
            /**
             *{@inheritdoc} 
             */

            public function submitForm(array &$form, FormStateInterface $form_state){

               $id = \Drupal::routeMatch()->getParameter('id');

               $postData = $form_state->getValues();
               /**
                * Remove the unwanted keys form postData
                */

                unset($postData['update'],$postData['form_build_id'],$postData['form_token'],$postData['form_id'],$postData['op']);
                $query = \Drupal::Database();
                $query->update('employee')->fields($postData)->condition('id',$id)->execute();
                $responce = new \Symfony\Component\HttpFoundation\RedirectResponse('../employee-list');
               //  echo "<pre>";print_r($postData );echo"</pre>";exit;
               \Drupal::messenger()->addMessage($this->t('Form Updated Successfully'), 'status', TRUE);
                
  
          
            }
     }
     
    