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
 // Plz test this module read this cooment code because according to your api odd number in their address and do not work on weekends. data is not avalible in this condition//

 
 //*if both below condition is true then data come from according to your requirement *//
 //if($matches==true && count($data_value['openingHours'])==5){`
 //*if streetadrees match in odd then data will show below contion is true then countryCode is DE and city is Dresden and postal code is 01067 *//
 if($matches==true){
  //*This condition is remove the weekend data and Put the countryCode is CZ and city is Prague and postal code is 11000*//
  //if(count($data_value['openingHours'])==5){
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