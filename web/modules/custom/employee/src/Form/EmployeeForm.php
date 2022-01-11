<?php
     namespace Drupal\employee\Form;
     use Drupal\Core\Form\FormBase;
     use Drupal\Core\Form\FormStateInterface;
     use Drupal\Code\Database\Database;

     class EmployeeForm extends FormBase{
          /**
           * {@inheritdoc}
           */

           public function getFormId() {
                return 'create_employee';
           }

           /**
            * {@inheritdoc}
            */

            public function buildForm(array $form, FormStateInterface $form_state){

               $genderOptions = array(
                    '0'=>'Select Gender',
                    'Male' =>'Male',
                    'Female'=>'Female',
                    'Other'=>'Other'
               );

               $form['name'] = array(
                    '#type' =>'textfield',
                    '#title'=>t('Name'),
                    '#default_value'=>'',
                    '#required' => true
               );

               $form['gender'] = array(
                    '#type' =>'select',
                    '#title'=>'Gender',
                    '#options'=>$genderOptions,
                    '#required' => true
               );

               $form['about_employee'] = array(
                    '#type' =>'textarea',
                    '#title'=>'About Employee',
                    '#default_value'=>'',
                    '#required' => true
               );

               $form['save'] = array(
                    '#type' =>'submit',
                    '#title'=>'Save Employee',
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

               $postData = $form_state->getValues();
               /**
                * Remove the unwanted keys form postData
                */

                unset($postData['save'],$postData['form_build_id'],$postData['form_token'],$postData['form_id'],$postData['op']);
                $query = \Drupal::Database();
                $query->insert('employee')->fields($postData)->execute();
               //  echo "<pre>";print_r($postData );echo"</pre>";exit;
               \Drupal::messenger()->addMessage($this->t('Form Submitted Successfully'), 'status', TRUE);
                
  
          
            }
     }
     ?>

    