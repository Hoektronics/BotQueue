<?php

namespace App\Models;

use App\ModelTraits\UuidKey;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use UuidKey;
}
