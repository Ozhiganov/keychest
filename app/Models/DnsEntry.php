<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 26.05.17
 * Time: 14:31
 */

namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class DnsEntry extends Model
{
    public $incrementing = true;

    protected $guarded = array();

    protected $table = 'scan_dns_entry';

    /**
     * Get the scan_id record for this result
     */
    public function dnsScan()
    {
        return $this->belongsTo('App\Model\DnsResult', 'scan_id');
    }

    public function getDates()
    {
        return array();
    }
}
