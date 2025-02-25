<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Stokopname_model extends CI_Model
{
  public $table;
  public $id;
  public $column_order;
  public $column_search;
  public $order;
  function __construct() {
    $this->table = 'stokopname';
    $this->id = 'id_stokopname';
    $this->column_order = array(null,'tanggal','kode_barang', 'nama_barang','jumlah_barang',  null);
    $this->column_search = array('tanggal','kode_barang', 'nama_barang','jumlah_barang');
    $this->order = array('id_stokopname' => 'asc');
  }
  public function getRows($postData,$role) {
    $this->_get_datatables_query($postData,$role);
    if ($postData['length'] != -1) {
      $this->db->limit($postData['length'], $postData['start']);
    }
    $query = $this->db->get();
    return $query->result();
  }

  public function countAll() {
    $this->db->from($this->table." as so");
    $this->db->join('master_barang mb','mb.id_barang = so.id_barang');
    return $this->db->count_all_results();
  }

  public function countFiltered($postData,$role) {
    $this->_get_datatables_query($postData,$role);
    $query = $this->db->get();
    return $query->num_rows();
  }

  private function _get_datatables_query($postData,$role) {
    $this->db->from($this->table." as so");
    $this->db->join('master_barang mb','mb.id_barang = so.id_barang');

    $i = 0;
    foreach ($this->column_search as $item) {
      if ($postData['search']['value']) {
        if ($i === 0) {
          $this->db->group_start();
          $this->db->like($item, $postData['search']['value']);
        } else {
          $this->db->or_like($item, $postData['search']['value']);
        }
        if (count($this->column_search) - 1 == $i) {
          $this->db->group_end();
        }
      }
      $i++;
    }
    if (isset($postData['order'])) {
      $this->db->order_by($this->column_order[$postData['order']['0']['column']], $postData['order']['0']['dir']);
    } else if (isset($this->order)) {
      $order = $this->order;
      $this->db->order_by(key($order), $order[key($order)]);
    }
  }
  public function tambah() {
    $data = $_POST;
    $detailBarang = $this->db->get_where('master_barang',['id_barang' => $data['id_barang']])->row_array();
    $data['kode_barang'] = $detailBarang['kode_barang'];
    unset($data['aksi']);
    // var_dump($data['tanggal']);
    // $tgl = str_replace("T"," ",$data['tanggal']);
    // var_dump($tgl);
    // var_dump(strtotime($data['tanggal']));
    // var_dump(strtotime($tgl));
    // exit;
    $cek = $this->db->get_where($this->table, [
      'kode_barang' => $data['kode_barang'],
      "DATE_FORMAT(tanggal, '%Y-%m-%d') =" => date('Y-m-d', strtotime($data['tanggal']))
  ])->row();
    if ($cek) {
          $hasil['status'] = false;
          $hasil['pesan'] = "Kode Barang sudah di Stok opname tanggal itu";
          return $hasil;
    }
    $this->db->insert($this->table, $data);

    $data = [
      'status' => true,
      'pesan' => "Data Stok Opname Berhasil Ditambah"
    ];

    return $data;
  }
  public function edit() {
    $data = $_POST;
    unset($data['aksi']);
    $this->db->where($this->id, $data['id']);
    unset($data['id']);

    $this->db->update($this->table, $data);

    $data = [
      'status' => true,
      'pesan' => "Data Stok Opname Berhasil diubah"
    ];

    return $data;
  }
  public function hapus() {
    $id = htmlspecialchars($_POST['id']);

    $this->db->where($this->id, $id);
    $this->db->delete($this->table);

    $data = [
      'status' => true,
      'pesan' => "Data Stok Opname Berhasil Dihapus"
    ];

    return $data;
  }
  public function getData(){
    $this->db->select('*');
    $this->db->from($this->table);
    $this->db->where($this->id,$_POST['id_stokopname']);
    $data = $this->db->get()->row();
    return $data;
  }
  public function getBarang(){
    $this->db->from("master_barang");
    return $this->db->get()->result_array();
  }
}?>