<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handler.php');

class APIUserInfoHandler extends APIHandler
{
  public function execute()
  {
    $db = $this->database();
    $mid = $this->motherID();

    $sql = "SELECT collecting_samples FROM `bbcs_v3`.`mother_studies` WHERE MID = '$mid'";
    $result = $db->query($sql);
    $row = $result->fetch_assoc();
    $collecting_samples = $row['collecting_samples'];
    $result->close();

    $collecting_samples = ($collecting_samples === 'Y') ? true : false;
    $accepted_consent_form = !$this->user_needs_to_consent();

    $user_details = array(
      'collecting_samples' => $collecting_samples,
      'accepted_consent_form' => $accepted_consent_form
    );

    return new APIJSONResult(array('user' => $user_details));
  }

  public function user_needs_to_consent()
  {
    $db = $this->database();
    $mid = $this->motherID();

    $query = <<<SQL
SELECT COUNT(*) AS count FROM
  mother_studies_type mst INNER JOIN mother_studies_econsent msec
    ON msec.MID = mst.MID
  WHERE
    mst.infoPump = msec.infoPump_consent AND
    mst.infoMilkRemoval = msec.infoMilkRemoval_consent AND
    mst.infoEffect = msec.infoEffect_consent AND
    mst.infoCellular = msec.infoCellular_consent AND
    mst.infoParticipation = msec.infoParticipation_consent AND
    mst.infoPerception = msec.infoPerception_consent AND
    mst.infoComposition = msec.infoComposition_consent AND
    mst.infoBrainwave = msec.infoBrainwave_consent AND
    mst.MID = ?;
SQL;

    $stmt = $db->prepare($query);

    $stmt->bind_param('s', $mid);

    if ($stmt->execute() === false)
      throw new APIError(APIError::USER_CONSENT_FETCH_FAILED);

    $stmt->bind_result($consent_count);

    if ($stmt->fetch() === false)
      throw new APIError(APIError::USER_CONSENT_FETCH_FAILED);

    $stmt->close();

    return ($consent_count == 0);
  }
}

?>