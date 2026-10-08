<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates all 24 Falcon Group employee emails to the format: firstname@falcon.com
     * and sets their password to 'falcon123'.
     */
    public function up(): void
    {
        $mapping = [
            'Ranjan Kumar Behera' => 'ranjan@falcon.com',
            'Surajit Satpathy' => 'surajit@falcon.com',
            'Anil Prasad Mohanty' => 'anil@falcon.com',
            'Gauri Shankar Rath' => 'gauri@falcon.com',
            'Sailesh Patnaik' => 'sailesh@falcon.com',
            'Anjan Mohanty' => 'anjan@falcon.com',
            'Swadesh Sarangi' => 'swadesh@falcon.com',
            'Kamala Kant Dash' => 'kamala@falcon.com',
            'Gaurav Kaushik' => 'gaurav@falcon.com',
            'Vikrant Gupta' => 'vikrant@falcon.com',
            'Jayanta Ghose' => 'jayanta@falcon.com',
            'Arun Kumar Mohanty' => 'arun@falcon.com',
            'Sai Prasad Dash' => 'sai@falcon.com',
            'Tapan Kumar Mohanty' => 'tapan@falcon.com',
            'Tupakula Suresh Babu' => 'tupakula@falcon.com',
            'Ashutosh Das' => 'ashutosh@falcon.com',
            'Snigdha Samir Mohapatra' => 'snigdha@falcon.com',
            'Chirag Bansal' => 'chirag@falcon.com',
            'Niranjan Mishra' => 'niranjan@falcon.com',
            'Biranchi Narayan Biswal' => 'biranchi@falcon.com',
            'Umesh Mohapatra' => 'umesh@falcon.com',
            'Susmita Dutta' => 'susmita@falcon.com',
            'Sunil Dora' => 'sunil@falcon.com',
            'Syed Zubenoor Ali' => 'syed@falcon.com',
        ];

        foreach ($mapping as $name => $newEmail) {
            $user = User::where('name', $name)->first();
            if ($user) {
                $user->update([
                    'email' => $newEmail,
                    'password' => Hash::make('falcon123'),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
