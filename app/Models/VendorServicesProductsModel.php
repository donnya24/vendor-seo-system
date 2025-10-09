<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorServicesProductsModel extends Model
{
    protected $table      = 'vendor_services_products';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'vendor_id',
        'service_name',
        'service_description',
        'product_name',
        'product_description',
        'price',
        'attachment',
        'attachment_url',
        'created_at',
        'updated_at',
    ];

    public function getGroupedServicesProducts($vendorId)
    {
        return $this->select("
                MIN(id) AS id,
                service_name,
                service_description,
                GROUP_CONCAT(product_name SEPARATOR '<br>')                  AS products,
                GROUP_CONCAT(product_description SEPARATOR '<br>')           AS products_deskripsi,
                GROUP_CONCAT(CAST(price AS UNSIGNED) SEPARATOR '<br>')       AS products_harga,
                GROUP_CONCAT(attachment SEPARATOR '<br>')                    AS products_lampiran,
                GROUP_CONCAT(attachment_url SEPARATOR '<br>')                AS products_lampiran_url,
                GROUP_CONCAT(id SEPARATOR ',')                               AS product_ids,
                MIN(created_at)                                              AS created_at,
                MAX(updated_at)                                              AS updated_at
            ")
            ->where('vendor_id', $vendorId)
            ->groupBy('service_name, service_description')
            ->findAll();
    }
}
