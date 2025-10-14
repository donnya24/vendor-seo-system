<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorProfilesModel extends Model
{
    protected $table            = 'vendor_profiles';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'user_id',
        'business_name',
        'owner_name', 
        'whatsapp_number',
        'phone',
        'profile_image',
        'commission_type',
        'requested_commission',
        'requested_commission_nominal',
        'commission_rate',
        'status',
        'rejection_reason',
        'inactive_reason',
        'approved_at',
        'action_by',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $returnType       = 'array';
    protected $dateFormat       = 'datetime';

    // Validation rules
    protected $validationRules = [
        'business_name'   => 'required|min_length[3]|max_length[150]',
        'owner_name'      => 'required|min_length[3]|max_length[100]',
        'whatsapp_number' => 'required|max_length[30]',
    ];

    protected $validationMessages = [
        'business_name' => [
            'required' => 'Nama bisnis harus diisi',
            'min_length' => 'Nama bisnis minimal 3 karakter',
            'max_length' => 'Nama bisnis maksimal 150 karakter'
        ],
        'owner_name' => [
            'required' => 'Nama pemilik harus diisi',
            'min_length' => 'Nama pemilik minimal 3 karakter',
            'max_length' => 'Nama pemilik maksimal 100 karakter'
        ],
        'whatsapp_number' => [
            'required' => 'Nomor WhatsApp harus diisi',
            'max_length' => 'Nomor WhatsApp maksimal 30 karakter'
        ]
    ];

    /**
     * Get vendor profile by user_id
     */
    public function getByUserId($userId)
    {
        return $this->where('user_id', (int)$userId)->first();
    }

    /**
     * Get vendors by status
     */
    public function getByStatus($status)
    {
        return $this->where('status', $status)->findAll();
    }

    /**
     * Get pending vendors
     */
    public function getPendingVendors()
    {
        return $this->where('status', 'pending')->findAll();
    }

    /**
     * Get verified vendors
     */
    public function getVerifiedVendors()
    {
        return $this->where('status', 'verified')->findAll();
    }

    /**
     * Get rejected vendors
     */
    public function getRejectedVendors()
    {
        return $this->where('status', 'rejected')->findAll();
    }

    /**
     * Get inactive vendors
     */
    public function getInactiveVendors()
    {
        return $this->where('status', 'inactive')->findAll();
    }

    /**
     * Update vendor status
     */
    public function updateStatus($vendorId, $status, $reason = null, $actionBy = null)
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($status === 'verified') {
            $data['approved_at'] = date('Y-m-d H:i:s');
            $data['action_by'] = $actionBy;
            $data['rejection_reason'] = null;
            $data['inactive_reason'] = null;
        } elseif ($status === 'rejected') {
            $data['rejection_reason'] = $reason;
            $data['approved_at'] = null;
            $data['inactive_reason'] = null;
        } elseif ($status === 'inactive') {
            $data['inactive_reason'] = $reason;
            $data['approved_at'] = null;
        }

        return $this->update($vendorId, $data);
    }

    /**
     * Update commission rate for verified vendor
     */
    public function updateCommissionRate($vendorId, $commissionRate)
    {
        return $this->update($vendorId, [
            'commission_rate' => $commissionRate,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Check if vendor exists by user_id
     */
    public function vendorExists($userId)
    {
        return $this->where('user_id', (int)$userId)->countAllResults() > 0;
    }

    /**
     * Get vendor with user data
     */
    public function getVendorWithUser($vendorId)
    {
        return $this->select('vendor_profiles.*, users.email, users.username')
                   ->join('users', 'users.id = vendor_profiles.user_id')
                   ->where('vendor_profiles.id', $vendorId)
                   ->first();
    }

    /**
     * Get all vendors with user data
     */
    public function getAllVendorsWithUser()
    {
        return $this->select('vendor_profiles.*, users.email, users.username')
                   ->join('users', 'users.id = vendor_profiles.user_id')
                   ->findAll();
    }

    /**
     * Search vendors by business name or owner name
     */
    public function searchVendors($keyword)
    {
        return $this->like('business_name', $keyword)
                   ->orLike('owner_name', $keyword)
                   ->findAll();
    }

    /**
     * Get vendors by commission type
     */
    public function getByCommissionType($commissionType)
    {
        return $this->where('commission_type', $commissionType)->findAll();
    }

    /**
     * Get vendors that need approval (pending status)
     */
    public function getVendorsNeedingApproval()
    {
        return $this->where('status', 'pending')
                   ->orderBy('created_at', 'ASC')
                   ->findAll();
    }

    /**
     * Count vendors by status
     */
    public function countByStatus($status = null)
    {
        if ($status) {
            return $this->where('status', $status)->countAllResults();
        }
        return $this->countAllResults();
    }

    /**
     * Get recent vendors
     */
    public function getRecentVendors($limit = 10)
    {
        return $this->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Insert dengan logging
     */
    public function insert($data = null, bool $returnID = true)
    {
        try {
            log_message('info', 'Inserting vendor profile: ' . json_encode($data));
            $result = parent::insert($data, $returnID);
            
            if ($result) {
                log_message('info', 'Vendor profile inserted successfully with ID: ' . $result);
            } else {
                log_message('error', 'Failed to insert vendor profile');
            }
            
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error inserting vendor profile: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update dengan logging
     */
    public function update($id = null, $data = null): bool
    {
        try {
            log_message('info', "Updating vendor profile ID: {$id} with data: " . json_encode($data));
            $result = parent::update($id, $data);
            
            if ($result) {
                log_message('info', 'Vendor profile updated successfully');
            } else {
                log_message('error', 'Failed to update vendor profile');
            }
            
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error updating vendor profile: ' . $e->getMessage());
            return false;
        }
    }
}