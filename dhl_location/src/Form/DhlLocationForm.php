<?php

namespace Drupal\dhl_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Client;
use Symfony\Component\Yaml\Yaml;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class DhlLocationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dhl_location_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['country'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Country'),
      '#required' => TRUE,
    ];

    $form['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#required' => TRUE,
    ];

    $form['postal_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Postal Code'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $country = $form_state->getValue('country');
    $city = $form_state->getValue('city');
    $postal_code = $form_state->getValue('postal_code');

// Send request to DHL API.

  
$client = new Client();
$response = $client->request('GET', 'https://api.dhl.com/location-finder/v1/find-by-address', [
    'headers' => [
      'DHL-API-Key' => 'demo-key',
    ],
    'query' => [
      'countryCode' => $country,
      'cityName' => $city,
      'postalCode' => $postal_code,
    ],
  ]);

  $data = json_decode($response->getBody(), TRUE);
  $adreessfilter=[];
  foreach($data['locations'] as $data_value)
{

  $str= $data_value['place']['address']['streetAddress'];
 $matches= $this->hasOddNumberInString($str);
 //dump(count($data_value['openingHours']));

 
 //if both condition is work then data come from according to requirement
 //if($matches==true && count($data_value['openingHours'])==5){
 //if condition streetadrees match
 //if($matches==true){
 //if condition is remove the weekend data.
  //if(count($data_value['openingHours'])==5){
 if(count($data_value['openingHours'])==5){
  $adreessfilter[]= $data_value;

 }
  
}

$yaml_output = Yaml::dump($adreessfilter);
\Drupal::messenger()->addStatus('<pre>' . $yaml_output . '</pre>');

return $yaml_output;

  }
public function hasOddNumberInString($str) {
  // Use regular expression to find all numbers in the string
  preg_match_all('/\d+/', $str, $matches);
  
  // Loop through all the found numbers
  foreach ($matches[0] as $number) {
      // Check if the number is odd

      if ($number % 2 !== 0) {
          return true;
      }
  }
    return false;

}


}