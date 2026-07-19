<?php
namespace App\Controllers;
// Load models
use App\Models\M_Admin;
use App\Models\M_Anggota;
use App\Models\M_Rak;
use App\Models\M_Kategori;
use App\Models\M_Buku;
use App\Models\M_Peminjaman;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevel;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class Admin extends BaseController
{
    public function login()
    {
        return view('Backend/Login/login');
    }

    public function dashboard()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            echo view('Backend/Template/header');
            echo view('Backend/Template/sidebar');
            echo view('Backend/Login/dashboard_admin');
            echo view('Backend/Template/footer');
        }
    }

    public function autentikasi(){
        $modelAdmin = new M_Admin; // proses inisiasi model
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $cekUsername = $modelAdmin->getDataAdmin(['username_admin' => $username, 'is_delete_admin' => '0'])->getNumRows();
        if($cekUsername == 0){
            session()->setFlashdata('error','Username Tidak Ditemukan!');
            ?>
            <script>
                history.go(-1);
            </script>
            <?php
        }
        else{
            $dataUser = $modelAdmin->getDataAdmin(['username_admin' => $username, 'is_delete_admin' => '0'])->getRowArray();
            $passwordUser = $dataUser['password_admin'];

            $verifikasiPassword = password_verify($password, $passwordUser);
            if(!$verifikasiPassword){
                session()->setFlashdata('error','Password Tidak Sesuai!');
                ?>
                <script>
                    history.go(-1);
                </script>
                <?php
            }
            else{
                $dataSession = [
                    'ses_id' => $dataUser['id_admin'],
                    'ses_user' => $dataUser['nama_admin'],
                    'ses_level' => $dataUser['akses_level']
                ];
                session()->set($dataSession);
                session()->setFlashdata('success','Login Berhasil!');
                ?>
                <script>
                    document.location = "<?= base_url('admin/dashboard-admin');?>";
                </script>
                <?php
            }
        }
    }

    public function logout(){
        session()->remove('ses_id');
        session()->remove('ses_user');
        session()->remove('ses_level');
        session()->setFlashdata('info','Anda telah keluar dari sistem!');
        ?>
        <script>
            document.location = "<?= base_url('admin/login-admin');?>";
        </script>
        <?php
    }

    public function master_data_admin(){
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $modelAdmin = new M_Admin; // inisiasi

            $uri = service('uri');
            $pages = $uri->getSegment(2);
            $dataUser = $modelAdmin->getDataAdmin(['is_delete_admin' => '0', 'akses_level !=' => '1'])->getResultArray();

            $data['pages'] = $pages;
            $data['data_user'] = $dataUser;

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/MasterAdmin/master-data-admin', $data);
            echo view('Backend/Template/footer', $data);
        }
    }

    public function input_data_admin(){
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            echo view('Backend/Template/header');
            echo view('Backend/Template/sidebar');
            echo view('Backend/MasterAdmin/input-admin');
            echo view('Backend/Template/footer');
        }
    }

    public function simpan_data_admin(){
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $modelAdmin = new M_Admin; // inisiasi

            $nama = $this->request->getPost('nama');
            $username = $this->request->getPost('username');
            $level = $this->request->getPost('level');

            $cekUname = $modelAdmin->getDataAdmin(['username_admin' => $username])->getNumRows();
            if($cekUname > 0){
                session()->setFlashdata('error','Username sudah digunakan!!');
                ?>
                <script>
                    history.go(-1);
                </script>
                <?php
            }
            else{
                $hasil = $modelAdmin->autoNumber()->getRowArray();
                if(!$hasil){
                    $id = "ADM001";
                }
                else{
                    $kode = $hasil['id_admin'];
                    $noUrut = (int) substr($kode, -3);
                    $noUrut++;
                    $id = "ADM".sprintf("%03s", $noUrut);
                }

                $dataSimpan = [
                    'id_admin' => $id,
                    'nama_admin' => $nama,
                    'username_admin' => $username,
                    'password_admin' => password_hash('pass_admin', PASSWORD_DEFAULT),
                    'akses_level' => $level,
                    'is_delete_admin' => '0',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $modelAdmin->saveDataAdmin($dataSimpan);
                session()->setFlashdata('success', 'Data Admin Berhasil Ditambahkan!!');
                ?>
                <script>
                    document.location = "<?= base_url('admin/master-data-admin');?>";
                </script>
                <?php
            }
        }
    }

    public function edit_data_admin(){
        $uri = service('uri');
        $idEdit = $uri->getSegment(3);
        $modelAdmin = new M_Admin;
        $dataAdmin = $modelAdmin->getDataAdmin(['sha1(id_admin)' => $idEdit])->getRowArray();
        session()->set(['idUpdate' => $dataAdmin['id_admin']]);

        $page = $uri->getSegment(2);

        $data['page'] = $page;
        $data['web_title'] = "Edit Data Admin";
        $data['data_admin'] = $dataAdmin;

        echo view('Backend/Template/header', $data);
        echo view('Backend/Template/sidebar', $data);
        echo view('Backend/MasterAdmin/edit-admin', $data);
        echo view('Backend/Template/footer', $data);
    }

    public function update_data_admin(){
        $modelAdmin = new M_Admin;

        $idUpdate = session()->get('idUpdate');
        $nama = $this->request->getPost('nama');
        $level = $this->request->getPost('level');

        if($nama=="" or $level==""){
            session()->setFlashdata('error','Isian tidak boleh kosong!!');
            ?>
            <script>
                history.go(-1);
            </script>
            <?php
        }
        else{
            $dataUpdate = [
                'nama_admin' => $nama,
                'akses_level' => $level,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $whereUpdate = ['id_admin' => $idUpdate];

            $modelAdmin->updateDataAdmin($dataUpdate, $whereUpdate);
            session()->remove('idUpdate');
            session()->setFlashdata('success', 'Data Admin Berhasil Diperbaharui!');
            ?>
            <script>
                document.location = "<?= base_url('admin/master-data-admin');?>";
            </script>
            <?php
        }
    }

    public function hapus_data_admin(){
        $modelAdmin = new M_Admin;

        $uri = service('uri');
        $idHapus = $uri->getSegment(3);

        $dataUpdate = [
            'is_delete_admin' => '1',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $whereUpdate = ['sha1(id_admin)' => $idHapus];
        $modelAdmin->updateDataAdmin($dataUpdate, $whereUpdate);
        session()->setFlashdata('success', 'Data Admin Berhasil Dihapus!');
        ?>
        <script>
            document.location = "<?= base_url('admin/master-data-admin');?>";
        </script>
        <?php
    }
    // Akhir modul admin

    // ===================== AWAL MODUL ANGGOTA =====================

    public function master_data_anggota()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $modelAnggota = new M_Anggota;

            $uri   = service('uri');
            $pages = $uri->getSegment(2);

            $dataAnggota = $modelAnggota->getDataAnggota(['is_delete_anggota' => '0'])->getResultArray();

            $data['pages']        = $pages;
            $data['web_title']    = "Master Data Anggota";
            $data['data_anggota'] = $dataAnggota;

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/MasterAnggota/master-data-anggota', $data);
            echo view('Backend/Template/footer', $data);
        }
    }

    public function input_data_anggota()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $uri  = service('uri');
            $page = $uri->getSegment(2);

            $data['page']      = $page;
            $data['web_title'] = "Input Data Anggota";

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/MasterAnggota/input-anggota', $data);
            echo view('Backend/Template/footer', $data);
        }
    }

    public function simpan_data_anggota()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $modelAnggota = new M_Anggota;

            $nama          = $this->request->getPost('nama');
            $jenis_kelamin = $this->request->getPost('jenis_kelamin');
            $no_tlp        = $this->request->getPost('no_tlp');
            $alamat        = $this->request->getPost('alamat');
            $email         = $this->request->getPost('email');
            $password      = $this->request->getPost('password');

            $hasil = $modelAnggota->autoNumber()->getRowArray();
            if(!$hasil){
                $id = "AGT001";
            }
            else{
                $kode   = $hasil['id_anggota'];
                $noUrut = (int) substr($kode, -3);
                $noUrut++;
                $id = "AGT".sprintf("%03s", $noUrut);
            }

            $dataSimpan = [
                'id_anggota'        => $id,
                'nama_anggota'      => ucwords($nama),
                'jenis_kelamin'     => $jenis_kelamin,
                'no_tlp'            => $no_tlp,
                'alamat'            => $alamat,
                'email'             => $email,
                'password_anggota'  => password_hash($password, PASSWORD_DEFAULT),
                'is_delete_anggota' => '0',
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s')
            ];

            $modelAnggota->saveDataAnggota($dataSimpan);
            session()->setFlashdata('success', 'Data Anggota Berhasil Ditambahkan!!');
            ?>
            <script>
                document.location = "<?= base_url('admin/master-data-anggota');?>";
            </script>
            <?php
        }
    }

    public function edit_data_anggota()
    {
        $uri          = service('uri');
        $idEdit       = $uri->getSegment(3);
        $modelAnggota = new M_Anggota;

        $dataAnggota = $modelAnggota->getDataAnggota(['sha1(id_anggota)' => $idEdit])->getRowArray();
        session()->set(['idUpdate' => $dataAnggota['id_anggota']]);

        $page = $uri->getSegment(2);

        $data['page']         = $page;
        $data['web_title']    = "Edit Data Anggota";
        $data['data_anggota'] = $dataAnggota;

        echo view('Backend/Template/header', $data);
        echo view('Backend/Template/sidebar', $data);
        echo view('Backend/MasterAnggota/edit-anggota', $data);
        echo view('Backend/Template/footer', $data);
    }

    public function update_data_anggota()
    {
        $modelAnggota = new M_Anggota;

        $idUpdate      = session()->get('idUpdate');
        $nama          = $this->request->getPost('nama');
        $jenis_kelamin = $this->request->getPost('jenis_kelamin');
        $no_tlp        = $this->request->getPost('no_tlp');
        $alamat        = $this->request->getPost('alamat');
        $email         = $this->request->getPost('email');

        if($nama=="" or $jenis_kelamin==""){
            session()->setFlashdata('error','Isian tidak boleh kosong!!');
            ?>
            <script>history.go(-1);</script>
            <?php
        }
        else{
            $dataUpdate = [
                'nama_anggota'  => ucwords($nama),
                'jenis_kelamin' => $jenis_kelamin,
                'no_tlp'        => $no_tlp,
                'alamat'        => $alamat,
                'email'         => $email,
                'updated_at'    => date('Y-m-d H:i:s')
            ];
            $whereUpdate = ['id_anggota' => $idUpdate];

            $modelAnggota->updateDataAnggota($dataUpdate, $whereUpdate);
            session()->remove('idUpdate');
            session()->setFlashdata('success', 'Data Anggota Berhasil Diperbaharui!');
            ?>
            <script>
                document.location = "<?= base_url('admin/master-data-anggota');?>";
            </script>
            <?php
        }
    }

    public function hapus_data_anggota()
    {
        $modelAnggota = new M_Anggota;

        $uri     = service('uri');
        $idHapus = $uri->getSegment(3);

        $dataUpdate = [
            'is_delete_anggota' => '1',
            'updated_at'        => date('Y-m-d H:i:s')
        ];
        $whereUpdate = ['sha1(id_anggota)' => $idHapus];

        $modelAnggota->updateDataAnggota($dataUpdate, $whereUpdate);
        session()->setFlashdata('success', 'Data Anggota Berhasil Dihapus!');
        ?>
        <script>
            document.location = "<?= base_url('admin/master-data-anggota');?>";
        </script>
        <?php
    }

    // ===================== AKHIR MODUL ANGGOTA =====================
    // ===================== AWAL MODUL RAK =====================

    public function master_data_rak()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $modelRak = new M_Rak;

            $uri   = service('uri');
            $pages = $uri->getSegment(2);

            $dataRak = $modelRak->getDataRak(['is_delete_rak' => '0'])->getResultArray();

            $data['pages']    = $pages;
            $data['web_title'] = "Master Data Rak";
            $data['data_rak'] = $dataRak;

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/MasterRak/master-data-rak', $data);
            echo view('Backend/Template/footer', $data);
        }
    }

    public function input_data_rak()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $uri  = service('uri');
            $page = $uri->getSegment(2);

            $data['page']      = $page;
            $data['web_title'] = "Input Data Rak";

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/MasterRak/input-rak', $data);
            echo view('Backend/Template/footer', $data);
        }
    }

    public function simpan_data_rak()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $modelRak = new M_Rak;

            $nama_rak = $this->request->getPost('nama_rak');

            $hasil = $modelRak->autoNumber()->getRowArray();
            if(!$hasil){
                $id = "RAK001";
            }
            else{
                $kode   = $hasil['id_rak'];
                $noUrut = (int) substr($kode, -3);
                $noUrut++;
                $id = "RAK".sprintf("%03s", $noUrut);
            }

            $dataSimpan = [
                'id_rak'        => $id,
                'nama_rak'      => ucwords($nama_rak),
                'is_delete_rak' => '0',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ];

            $modelRak->saveDataRak($dataSimpan);
            session()->setFlashdata('success', 'Data Rak Berhasil Ditambahkan!!');
            ?>
            <script>
                document.location = "<?= base_url('admin/master-data-rak');?>";
            </script>
            <?php
        }
    }

    public function edit_data_rak()
    {
        $uri      = service('uri');
        $idEdit   = $uri->getSegment(3);
        $modelRak = new M_Rak;

        $dataRak = $modelRak->getDataRak(['sha1(id_rak)' => $idEdit])->getRowArray();
        session()->set(['idUpdate' => $dataRak['id_rak']]);

        $page = $uri->getSegment(2);

        $data['page']     = $page;
        $data['web_title'] = "Edit Data Rak";
        $data['data_rak'] = $dataRak;

        echo view('Backend/Template/header', $data);
        echo view('Backend/Template/sidebar', $data);
        echo view('Backend/MasterRak/edit-rak', $data);
        echo view('Backend/Template/footer', $data);
    }

    public function update_data_rak()
    {
        $modelRak = new M_Rak;

        $idUpdate = session()->get('idUpdate');
        $nama_rak = $this->request->getPost('nama_rak');

        if($nama_rak==""){
            session()->setFlashdata('error','Isian tidak boleh kosong!!');
            ?>
            <script>history.go(-1);</script>
            <?php
        }
        else{
            $dataUpdate = [
                'nama_rak'   => ucwords($nama_rak),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $whereUpdate = ['id_rak' => $idUpdate];

            $modelRak->updateDataRak($dataUpdate, $whereUpdate);
            session()->remove('idUpdate');
            session()->setFlashdata('success', 'Data Rak Berhasil Diperbaharui!');
            ?>
            <script>
                document.location = "<?= base_url('admin/master-data-rak');?>";
            </script>
            <?php
        }
    }

    public function hapus_data_rak()
    {
        $modelRak = new M_Rak;

        $uri     = service('uri');
        $idHapus = $uri->getSegment(3);

        $dataUpdate = [
            'is_delete_rak' => '1',
            'updated_at'    => date('Y-m-d H:i:s')
        ];
        $whereUpdate = ['sha1(id_rak)' => $idHapus];

        $modelRak->updateDataRak($dataUpdate, $whereUpdate);
        session()->setFlashdata('success', 'Data Rak Berhasil Dihapus!');
        ?>
        <script>
            document.location = "<?= base_url('admin/master-data-rak');?>";
        </script>
        <?php
    }

    // ===================== AKHIR MODUL RAK =====================
    // ===================== AWAL MODUL KATEGORI =====================

    public function master_data_kategori()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $modelKategori = new M_Kategori;

            $uri   = service('uri');
            $pages = $uri->getSegment(2);

            $dataKategori = $modelKategori->getDataKategori(['is_delete_kategori' => '0'])->getResultArray();

            $data['pages']         = $pages;
            $data['web_title']     = "Master Data Kategori";
            $data['data_kategori'] = $dataKategori;

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/MasterKategori/master-data-kategori', $data);
            echo view('Backend/Template/footer', $data);
        }
    }

    public function input_data_kategori()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $uri  = service('uri');
            $page = $uri->getSegment(2);

            $data['page']      = $page;
            $data['web_title'] = "Input Data Kategori";

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/MasterKategori/input-kategori', $data);
            echo view('Backend/Template/footer', $data);
        }
    }

    public function simpan_data_kategori()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $modelKategori = new M_Kategori;

            $nama_kategori = $this->request->getPost('nama_kategori');

            $hasil = $modelKategori->autoNumber()->getRowArray();
            if(!$hasil){
                $id = "KAT001";
            }
            else{
                $kode   = $hasil['id_kategori'];
                $noUrut = (int) substr($kode, -3);
                $noUrut++;
                $id = "KAT".sprintf("%03s", $noUrut);
            }

            $dataSimpan = [
                'id_kategori'        => $id,
                'nama_kategori'      => ucwords($nama_kategori),
                'is_delete_kategori' => '0',
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s')
            ];

            $modelKategori->saveDataKategori($dataSimpan);
            session()->setFlashdata('success', 'Data Kategori Berhasil Ditambahkan!!');
            ?>
            <script>
                document.location = "<?= base_url('admin/master-data-kategori');?>";
            </script>
            <?php
        }
    }

    public function edit_data_kategori()
    {
        $uri           = service('uri');
        $idEdit        = $uri->getSegment(3);
        $modelKategori = new M_Kategori;

        $dataKategori = $modelKategori->getDataKategori(['sha1(id_kategori)' => $idEdit])->getRowArray();
        session()->set(['idUpdate' => $dataKategori['id_kategori']]);

        $page = $uri->getSegment(2);

        $data['page']          = $page;
        $data['web_title']     = "Edit Data Kategori";
        $data['data_kategori'] = $dataKategori;

        echo view('Backend/Template/header', $data);
        echo view('Backend/Template/sidebar', $data);
        echo view('Backend/MasterKategori/edit-kategori', $data);
        echo view('Backend/Template/footer', $data);
    }

    public function update_data_kategori()
    {
        $modelKategori = new M_Kategori;

        $idUpdate      = session()->get('idUpdate');
        $nama_kategori = $this->request->getPost('nama_kategori');

        if($nama_kategori==""){
            session()->setFlashdata('error','Isian tidak boleh kosong!!');
            ?>
            <script>history.go(-1);</script>
            <?php
        }
        else{
            $dataUpdate = [
                'nama_kategori' => ucwords($nama_kategori),
                'updated_at'    => date('Y-m-d H:i:s')
            ];
            $whereUpdate = ['id_kategori' => $idUpdate];

            $modelKategori->updateDataKategori($dataUpdate, $whereUpdate);
            session()->remove('idUpdate');
            session()->setFlashdata('success', 'Data Kategori Berhasil Diperbaharui!');
            ?>
            <script>
                document.location = "<?= base_url('admin/master-data-kategori');?>";
            </script>
            <?php
        }
    }

    public function hapus_data_kategori()
    {
        $modelKategori = new M_Kategori;

        $uri     = service('uri');
        $idHapus = $uri->getSegment(3);

        $dataUpdate = [
            'is_delete_kategori' => '1',
            'updated_at'         => date('Y-m-d H:i:s')
        ];
        $whereUpdate = ['sha1(id_kategori)' => $idHapus];

        $modelKategori->updateDataKategori($dataUpdate, $whereUpdate);
        session()->setFlashdata('success', 'Data Kategori Berhasil Dihapus!');
        ?>
        <script>
            document.location = "<?= base_url('admin/master-data-kategori');?>";
        </script>
        <?php
    }

    // ===================== AKHIR MODUL KATEGORI =====================
// ===================== AWAL MODUL BUKU =====================

    public function master_buku()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $modelBuku = new M_Buku;
            // Mengambil data keseluruhan buku dari table buku di database
            $dataBuku = $modelBuku->getDataBukuJoin(['tbl_buku.is_delete_buku' => '0'])->getResultArray();

            $uri  = service('uri');
            $page = $uri->getSegment(2);

            $data['page']      = $page;
            $data['web_title'] = "Master Data Buku";
            $data['dataBuku']  = $dataBuku;

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/MasterBuku/master-data-buku', $data);
            echo view('Backend/Template/footer', $data);
        }
    }

    public function input_buku()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $modelKategori = new M_Kategori;
            $modelRak      = new M_Rak;
            $uri           = service('uri');
            $page          = $uri->getSegment(2);

            $data['page']          = $page;
            $data['web_title']     = "Input Data Buku";
            $data['data_kategori'] = $modelKategori->getDataKategori(['is_delete_kategori' => '0'])->getResultArray();
            $data['data_rak']      = $modelRak->getDataRak(['is_delete_rak' => '0'])->getResultArray();

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/MasterBuku/input-buku', $data);
            echo view('Backend/Template/footer', $data);
        }
    }

    public function simpan_buku()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $modelBuku = new M_Buku;

            $judulBuku       = $this->request->getPost('judul_buku');
            $pengarang       = $this->request->getPost('pengarang');
            $penerbit        = $this->request->getPost('penerbit');
            $tahun           = $this->request->getPost('tahun');
            $jumlahEksemplar = $this->request->getPost('jumlah_eksemplar');
            $kategoriBuku    = $this->request->getPost('kategori_buku');
            $keterangan      = $this->request->getPost('keterangan');
            $rak             = $this->request->getPost('rak');

            if(!$this->validate([
                'cover_buku' => 'uploaded[cover_buku]|max_size[cover_buku, 1024]|ext_in[cover_buku,jpg,jpeg,png]',
            ])){
                session()->setFlashdata('error', "Format file yang diizinkan : jpg, jpeg, png dengan maksimal ukuran 1 MB");
                return redirect()->to('/admin/input-buku')->withInput();
            }

            if(!$this->validate([
                'e_book' => 'uploaded[e_book]|max_size[e_book, 10240]|ext_in[e_book,pdf]',
            ])){
                session()->setFlashdata('error', "Format file yang diizinkan : pdf dengan maksimal ukuran 10 MB");
                return redirect()->to('/admin/input-buku')->withInput();
            }

            $coverBuku = $this->request->getFile('cover_buku');
            $ext1      = $coverBuku->getClientExtension();
            $namaFile1 = "Cover-Buku-".date("ymdHis").".".$ext1;
            $coverBuku->move('Assets/CoverBuku',$namaFile1);

            $eBook     = $this->request->getFile('e_book');
            $ext2      = $eBook->getClientExtension();
            $namaFile2 = "E-Book-".date("ymdHis").".".$ext2;
            $eBook->move('Assets/E-Book',$namaFile2);

            $hasil = $modelBuku->autoNumber()->getRowArray();
            if(!$hasil){
                $id = "BKU001";
            }
            else{
                $kode   = $hasil['id_buku'];
                $noUrut = (int) substr($kode, -3);
                $noUrut++;
                $id = "BKU".sprintf("%03s", $noUrut);
            }

            $dataSimpan = [
                'id_buku'          => $id,
                'judul_buku'       => ucwords($judulBuku),
                'pengarang'        => ucwords($pengarang),
                'penerbit'         => ucwords($penerbit),
                'tahun'            => $tahun,
                'jumlah_eksemplar' => $jumlahEksemplar,
                'id_kategori'      => $kategoriBuku,
                'keterangan'       => $keterangan,
                'id_rak'           => $rak,
                'cover_buku'       => $namaFile1,
                'e_book'           => $namaFile2,
                'is_delete_buku'   => '0',
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s')
            ];

            $modelBuku->saveDataBuku($dataSimpan);
            session()->setFlashdata('success', 'Data Buku Berhasil Diperbaharui!');
            ?>
            <script>
                document.location = "<?= base_url('admin/master-buku');?>";
            </script>
            <?php
        }
    }

    public function edit_buku()
    {
        $uri       = service('uri');
        $idEdit    = $uri->getSegment(3);
        $modelBuku = new M_Buku;

        $dataBuku = $modelBuku->getDataBukuJoin(['sha1(tbl_buku.id_buku)' => $idEdit])->getRowArray();
        session()->set(['idUpdate' => $dataBuku['id_buku']]);

        $modelKategori = new M_Kategori;
        $modelRak      = new M_Rak;

        $page = $uri->getSegment(2);

        $data['page']          = $page;
        $data['web_title']     = "Edit Data Buku";
        $data['data_buku']     = $dataBuku;
        $data['data_kategori'] = $modelKategori->getDataKategori(['is_delete_kategori' => '0'])->getResultArray();
        $data['data_rak']      = $modelRak->getDataRak(['is_delete_rak' => '0'])->getResultArray();

        echo view('Backend/Template/header', $data);
        echo view('Backend/Template/sidebar', $data);
        echo view('Backend/MasterBuku/edit-buku', $data);
        echo view('Backend/Template/footer', $data);
    }

    public function update_buku()
    {
        $modelBuku = new M_Buku;

        $idUpdate        = session()->get('idUpdate');
        $judulBuku       = $this->request->getPost('judul_buku');
        $pengarang       = $this->request->getPost('pengarang');
        $penerbit        = $this->request->getPost('penerbit');
        $tahun           = $this->request->getPost('tahun');
        $jumlahEksemplar = $this->request->getPost('jumlah_eksemplar');
        $kategoriBuku    = $this->request->getPost('kategori_buku');
        $keterangan      = $this->request->getPost('keterangan');
        $rak             = $this->request->getPost('rak');

        $dataBukuLama = $modelBuku->getDataBuku(['id_buku' => $idUpdate])->getRowArray();

        $coverBuku = $this->request->getFile('cover_buku');
        $eBook     = $this->request->getFile('e_book');

        if($coverBuku->getSize() > 0){
            if(!$this->validate([
                'cover_buku' => 'max_size[cover_buku, 1024]|ext_in[cover_buku,jpg,jpeg,png]',
            ])){
                session()->setFlashdata('error', "Format file yang diizinkan : jpg, jpeg, png dengan maksimal ukuran 1 MB");
                return redirect()->to('/admin/edit-buku/'.sha1($idUpdate))->withInput();
            }
            unlink('Assets/CoverBuku/'.$dataBukuLama['cover_buku']);
            $ext1      = $coverBuku->getClientExtension();
            $namaFile1 = "Cover-Buku-".date("ymdHis").".".$ext1;
            $coverBuku->move('Assets/CoverBuku',$namaFile1);
        } else {
            $namaFile1 = $dataBukuLama['cover_buku'];
        }

        if($eBook->getSize() > 0){
            if(!$this->validate([
                'e_book' => 'max_size[e_book, 10240]|ext_in[e_book,pdf]',
            ])){
                session()->setFlashdata('error', "Format file yang diizinkan : pdf dengan maksimal ukuran 10 MB");
                return redirect()->to('/admin/edit-buku/'.sha1($idUpdate))->withInput();
            }
            unlink('Assets/E-Book/'.$dataBukuLama['e_book']);
            $ext2      = $eBook->getClientExtension();
            $namaFile2 = "E-Book-".date("ymdHis").".".$ext2;
            $eBook->move('Assets/E-Book',$namaFile2);
        } else {
            $namaFile2 = $dataBukuLama['e_book'];
        }

        $dataUpdate = [
            'judul_buku'       => ucwords($judulBuku),
            'pengarang'        => ucwords($pengarang),
            'penerbit'         => ucwords($penerbit),
            'tahun'            => $tahun,
            'jumlah_eksemplar' => $jumlahEksemplar,
            'id_kategori'      => $kategoriBuku,
            'keterangan'       => $keterangan,
            'id_rak'           => $rak,
            'cover_buku'       => $namaFile1,
            'e_book'           => $namaFile2,
            'updated_at'       => date('Y-m-d H:i:s')
        ];
        $whereUpdate = ['id_buku' => $idUpdate];

        $modelBuku->updateDataBuku($dataUpdate, $whereUpdate);
        session()->remove('idUpdate');
        session()->setFlashdata('success', 'Data Buku Berhasil Diperbaharui!');
        ?>
        <script>
            document.location = "<?= base_url('admin/master-buku');?>";
        </script>
        <?php
    }

    public function hapus_buku()
    {
        $modelBuku = new M_Buku;

        $uri     = service('uri');
        $idHapus = $uri->getSegment(3);

        $dataHapus = $modelBuku->getDataBuku(['sha1(id_buku)' => $idHapus])->getRowArray();
        unlink('Assets/CoverBuku/'.$dataHapus['cover_buku']); // hapus file yang lama
        unlink('Assets/E-Book/'.$dataHapus['e_book']); // hapus file yang lama

        $modelBuku->hapusDataBuku(['sha1(id_buku)' => $idHapus]);
        session()->setFlashdata('success', 'Data Buku Berhasil Dihapus!');
        ?>
        <script>
            document.location = "<?= base_url('admin/master-buku');?>";
        </script>
        <?php
    }

    // ===================== AKHIR MODUL BUKU =====================
// ===================== AWAL MODUL PEMINJAMAN =====================

    public function peminjaman_step1()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $uri  = service('uri');
            $page = $uri->getSegment(2);

            $data['page']      = $page;
            $data['web_title'] = "Transaksi Peminjaman";

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/Transaksi/peminjaman-step-1', $data);
            echo view('Backend/Template/footer', $data);
        }
    }

    public function peminjaman_step2()
    {
        $modelAnggota    = new M_Anggota;
        $modelBuku       = new M_Buku;
        $modelPeminjaman = new M_Peminjaman;
        $uri             = service('uri');
        $page            = $uri->getSegment(2);

        if($this->request->getPost('id_anggota')){
            $idAnggota = $this->request->getPost('id_anggota');
            session()->set(['idAgt' => $idAnggota]);
        }
        else{
            $idAnggota = session()->get('idAgt');
        }

        $cekPeminjaman = $modelPeminjaman->getDataPeminjaman(['id_anggota' => $idAnggota, 'status_transaksi' => 'Berjalan'])->getNumRows();
        if($cekPeminjaman > 0){
            session()->setFlashdata('error','Transaksi Tidak Dapat Dilakukan, Masih Ada Transaksi Peminjaman yang Belum Diselesaikan!!');
            ?>
            <script>
                history.go(-1);
            </script>
            <?php
        }
        else{
            $dataAnggota = $modelAnggota->getDataAnggota(['id_anggota' => $idAnggota])->getRowArray();
            $dataBuku    = $modelBuku->getDataBukuJoin()->getResultArray();

            $jumlahTemp = $modelPeminjaman->getDataTemp(['id_anggota' => $idAnggota])->getNumRows();
            $data['jumlahTemp'] = $jumlahTemp;
            // Mengambil data keseluruhan buku dari table buku di database

            $dataTemp = $modelPeminjaman->getDataTempJoin(['tbl_temp_peminjaman.id_anggota' => $idAnggota])->getResultArray();

            $data['page']        = $page;
            $data['web_title']   = "Transaksi Peminjaman";
            $data['dataAnggota'] = $dataAnggota;
            $data['dataBuku']    = $dataBuku;
            $data['dataTemp']    = $dataTemp;

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/Transaksi/peminjaman-step-2', $data);
            echo view('Backend/Template/footer', $data);
        }
    }

    public function simpan_temp_pinjam()
    {
        $modelPeminjaman = new M_Peminjaman;
        $modelBuku       = new M_Buku;

        $uri     = service('uri');
        $idBuku  = $uri->getSegment(3);
        $dataBuku = $modelBuku->getDataBuku(['sha1(id_buku)' => $idBuku])->getRowArray();

        $adaTemp      = $modelPeminjaman->getDataTemp(['sha1(id_buku)' => $idBuku])->getNumRows();
        $adaBerjalan  = $modelPeminjaman->getDataPeminjaman(['id_anggota' => session()->get('idAgt'), 'status_transaksi' => 'Berjalan'])->getNumRows();
        if($adaTemp){
            session()->setFlashdata('error','Satu Anggota Hanya Bisa Meminjam 1 Buku dengan Judul yang Sama!');
            ?>
            <script>
                history.go(-1);
            </script>
            <?php
        }
        elseif($adaBerjalan){
            session()->setFlashdata('error','Masih ada transaksi peminjaman yang belum diselesaikan, silakan selesaikan peminjaman sebelumnya terlebih dahulu!');
            ?>
            <script>
                history.go(-1);
            </script>
            <?php
        }
        else{
            $dataSimpanTemp = [
                'id_anggota'   => session()->get('idAgt'),
                'id_buku'      => $dataBuku['id_buku'],
                'jumlah_temp'  => '1'
            ];
            $modelPeminjaman->saveDataTemp($dataSimpanTemp);
            $stok        = $dataBuku['jumlah_eksemplar']-1;
            $dataUpdate  = [
                'jumlah_eksemplar' => $stok
            ];
            $modelBuku->updateDataBuku($dataUpdate,['sha1(id_buku)' => $idBuku]);
            ?>
            <script>
                document.location = "<?= base_url('admin/peminjaman-step-2');?>";
            </script>
            <?php
        }
    }

    public function hapus_peminjaman()
    {
        $modelPeminjaman = new M_Peminjaman;
        $modelBuku       = new M_Buku;

        $uri    = service('uri');
        $idBuku = $uri->getSegment(3);
        $dataBuku = $modelBuku->getDataBuku(['sha1(id_buku)' => $idBuku])->getRowArray();

        $modelPeminjaman->hapusDataTemp(['sha1(id_buku)' => $idBuku, 'id_anggota' => session()->get('idAgt')]);
        $stok       = $dataBuku['jumlah_eksemplar']+1;
        $dataUpdate = [
            'jumlah_eksemplar' => $stok
        ];
        $modelBuku->updateDataBuku($dataUpdate,['sha1(id_buku)' => $idBuku]);
        ?>
        <script>
            document.location = "<?= base_url('admin/peminjaman-step-2');?>";
        </script>
        <?php
    }

    public function simpan_transaksi_peminjaman()
    {
        $modelPeminjaman = new M_Peminjaman;
        $idPeminjaman    = date("ymdHis");
        $time_sekarang   = time();
        $kembali         = date("Y-m-d", strtotime("+7 days", $time_sekarang));
        $jumlahPinjam    = $modelPeminjaman->getDataTemp(['id_anggota' => session()->get('idAgt')])->getNumRows();

        $dataQR  = $idPeminjaman;
        $labelQR = $idPeminjaman;
        $result  = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($dataQR)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->logoPath(FCPATH.'Assets/logo_ubsi.png')
            ->logoResizeToWidth(50)
            ->logoPunchoutBackground(true)
            ->labelText($labelQR)
            ->labelFont(new NotoSans(20))
            ->labelAlignment(LabelAlignment::Center)
            ->validateResult(false)
            ->build();

        // Directly output the QR code
        header('Content-Type: '.$result->getMimeType());

        // Save it to a file
        $namaQR = "qr_".$idPeminjaman.".png";
        $result->saveToFile(FCPATH.'Assets/qr_code/'.$namaQR);

        $dataSimpan = [
            'no_peminjaman'    => $idPeminjaman,
            'id_anggota'       => session()->get('idAgt'),
            'tgl_pinjam'       => date("Y-m-d"),
            'total_pinjam'     => $jumlahPinjam,
            'id_admin'         => '-',
            'status_transaksi' => 'Berjalan',
            'status_ambil_buku'=> 'Sudah Diambil',
            'qr_code'          => $namaQR
        ];
        $modelPeminjaman->saveDataPeminjaman($dataSimpan);

        $dataTemp = $modelPeminjaman->getDataTemp(['id_anggota' => session()->get('idAgt')])->getResultArray();
        foreach($dataTemp as $sementara){
            $simpanDetail = [
                'no_peminjaman' => $idPeminjaman,
                'id_buku'       => $sementara['id_buku'],
                'status_pinjam' => 'Sedang Dipinjam',
                'perpanjangan'  => '2',
                'tgl_kembali'   => $kembali
            ];
            $modelPeminjaman->saveDataDetail($simpanDetail);
        }

        $modelPeminjaman->hapusDataTemp(['id_anggota' => session()->get('idAgt')]);
        session()->remove('idAgt');
        session()->setFlashdata('success','Data Peminjaman Buku Berhasil Disimpan!')
        ?>
        <script>
            document.location = "<?= base_url('admin/data-transaksi-peminjaman');?>";
        </script>
        <?php
    }

    public function data_transaksi_peminjaman()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $modelPeminjaman = new M_Peminjaman;

            $uri  = service('uri');
            $page = $uri->getSegment(2);

            $dataPeminjaman = $modelPeminjaman->getDataPeminjamanJoin()->getResultArray();

            $data['page']           = $page;
            $data['web_title']      = "Data Transaksi Peminjaman";
            $data['dataPeminjaman'] = $dataPeminjaman;

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/Transaksi/data-transaksi-peminjaman', $data);
            echo view('Backend/Template/footer', $data);
        }
    }

    // ===================== AKHIR MODUL PEMINJAMAN =====================
public function detail_peminjaman()
    {
        if(session()->get('ses_id')=='' or session()->get('ses_user')=='' or session()->get('ses_level')==''){
            session()->setFlashdata('error','Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin');?>";
            </script>
            <?php
        }
        else{
            $modelPeminjaman = new M_Peminjaman;
            $modelBuku       = new M_Buku;

            $uri    = service('uri');
            $idDetail = $uri->getSegment(3);
            $page   = $uri->getSegment(2);

            $dataPeminjaman = $modelPeminjaman->getDataPeminjamanJoin(['sha1(tbl_peminjaman.no_peminjaman)' => $idDetail])->getRowArray();

            $dataDetail = $modelPeminjaman->getDataTempJoin(['tbl_detail_peminjaman.no_peminjaman' => $dataPeminjaman['no_peminjaman']])->getResultArray();

            $data['page']           = $page;
            $data['web_title']      = "Detail Peminjaman";
            $data['dataPeminjaman'] = $dataPeminjaman;
            $data['dataDetail']     = $dataDetail;

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/Transaksi/detail-peminjaman', $data);
            echo view('Backend/Template/footer', $data);
        }
    }
}
?>
