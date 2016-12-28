 <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DeliveryDetail extends CI_Model {

	public function __construct()
	{
		parent::__construct ();
		$this->id = -1;
        $this->name = 'DeliveryDetail';

	}
	
	public function prepare($id)
	{
		$this->data = null;
		$this->db2->from($this->name);
		$this->db2->where('id', $id);
		$this->data = $this->db2->row();
		if ($this->data) {
			$this->id = $id;
			return $this->data;
		} else {
			return null;
		}

	}

	public function create($d, $aid, $BatchID)
    {
        $save = array(
            "batchID" => $BatchID,
            "JID" => $d->JID,
            "CID" => $d->CID,
            "Year" => $d->Year,
            "No" => $d->No,
            "Counts" => $d->RealCounts,
            "DeliveStatus" => 1,
            "DeliveryTime" => date('Y-m-d h:i:s'),
            "Volume" => "",
            "Note" => "已发",
            "YingFa" => $d->NeedCounts,
            'DaiFa' => $d->NeedCounts - $d->RealCounts
        );
        $this->db2->insert($this->name, $save);
        return $this->db2->insert_id ();
    }
}
