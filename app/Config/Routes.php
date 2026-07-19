<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Admin::login');
$routes->get('/home/coba-parameter/(:alpha)/(:num)/(:alphanum)', 'Home::belajar_segment/$1/$2/$3');

// Routes untuk login admin
$routes->get('/admin/login-admin', 'Admin::login');
$routes->post('/admin/autentikasi-login', 'Admin::autentikasi');
$routes->get('/admin/dashboard-admin', 'Admin::dashboard');
$routes->get('/admin/logout', 'Admin::logout');

// Routes untuk module admin
$routes->get('/admin/master-data-admin', 'Admin::master_data_admin');
$routes->get('/admin/input-data-admin', 'Admin::input_data_admin');
$routes->post('/admin/simpan-admin', 'Admin::simpan_data_admin');
$routes->get('/admin/edit-data-admin/(:alphanum)', 'Admin::edit_data_admin/$1');
$routes->post('/admin/update-admin', 'Admin::update_data_admin');
$routes->get('/admin/hapus-data-admin/(:alphanum)', 'Admin::hapus_data_admin/$1');

// Routes untuk module anggota
$routes->get('/admin/master-data-anggota', 'Admin::master_data_anggota');
$routes->get('/admin/input-data-anggota', 'Admin::input_data_anggota');
$routes->post('/admin/simpan-anggota', 'Admin::simpan_data_anggota');
$routes->get('/admin/edit-data-anggota/(:alphanum)', 'Admin::edit_data_anggota/$1');
$routes->post('/admin/update-anggota', 'Admin::update_data_anggota');
$routes->get('/admin/hapus-data-anggota/(:alphanum)', 'Admin::hapus_data_anggota/$1');

// Routes untuk module rak
$routes->get('/admin/master-data-rak', 'Admin::master_data_rak');
$routes->get('/admin/input-data-rak', 'Admin::input_data_rak');
$routes->post('/admin/simpan-rak', 'Admin::simpan_data_rak');
$routes->get('/admin/edit-data-rak/(:alphanum)', 'Admin::edit_data_rak/$1');
$routes->post('/admin/update-rak', 'Admin::update_data_rak');
$routes->get('/admin/hapus-data-rak/(:alphanum)', 'Admin::hapus_data_rak/$1');

// Routes untuk module kategori
$routes->get('/admin/master-data-kategori', 'Admin::master_data_kategori');
$routes->get('/admin/input-data-kategori', 'Admin::input_data_kategori');
$routes->post('/admin/simpan-kategori', 'Admin::simpan_data_kategori');
$routes->get('/admin/edit-data-kategori/(:alphanum)', 'Admin::edit_data_kategori/$1');
$routes->post('/admin/update-kategori', 'Admin::update_data_kategori');
$routes->get('/admin/hapus-data-kategori/(:alphanum)', 'Admin::hapus_data_kategori/$1');

// Routes untuk module buku
$routes->get('/admin/master-buku', 'Admin::master_buku');
$routes->get('/admin/input-buku', 'Admin::input_buku');
$routes->post('/admin/simpan-buku', 'Admin::simpan_buku');
$routes->get('/admin/edit-buku/(:alphanum)', 'Admin::edit_buku/$1');
$routes->post('/admin/update-buku', 'Admin::update_buku');
$routes->get('/admin/hapus-buku/(:alphanum)', 'Admin::hapus_buku/$1');

// Routes untuk module peminjaman
$routes->get('/admin/data-transaksi-peminjaman', 'Admin::data_transaksi_peminjaman');
$routes->get('/admin/peminjaman-step-1', 'Admin::peminjaman_step1');
$routes->get('/admin/tes-qr', 'Admin::tes_qr');
$routes->get('/admin/peminjaman-step-2', 'Admin::peminjaman_step2');
$routes->post('/admin/peminjaman-step-2', 'Admin::peminjaman_step2');
$routes->get('/admin/simpan-temp-pinjam/(:alphanum)', 'Admin::simpan_temp_pinjam/$1');
$routes->get('/admin/hapus-temp/(:alphanum)', 'Admin::hapus_peminjaman/$1');
$routes->get('/admin/simpan-transaksi-peminjaman', 'Admin::simpan_transaksi_peminjaman');

// Routes untuk detail peminjaman
$routes->get('/admin/detail-peminjaman/(:alphanum)', 'Admin::detail_peminjaman/$1');