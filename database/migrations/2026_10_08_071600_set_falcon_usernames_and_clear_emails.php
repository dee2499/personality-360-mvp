<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sets username = firstname@falcon for all 24 Falcon employees,
     * and clears their email address (sets email = NULL).
     */
    public function up(): void
    {
        $mapping = [
            'Ranjan Kumar Behera' => 'ranjan@falcon',
            'Surajit Satpathy' => 'surajit@falcon',
            'Anil Prasad Mohanty' => 'anil@falcon',
            'Gauri Shankar Rath' => 'gauri@falcon',
            'Sailesh Patnaik' => 'sailesh@falcon',
            'Anjan Mohanty' => 'anjan@falcon',
            'Swadesh Sarangi' => 'swadesh@falcon',
            'Kamala Kant Dash' => 'kamala@falcon',
            'Gaurav Kaushik' => 'gaurav@falcon',
            'Vikrant Gupta' => 'vikrant@falcon',
            'Jayanta Ghose' => 'jayanta@falcon',
            'Arun Kumar Mohanty' => 'arun@falcon',
            'Sai Prasad Dash' => 'sai@falcon',
            'Tapan Kumar Mohanty' => 'tapan@falcon',
            'Tupakula Suresh Babu' => 'tupakula@falcon',
            'Ashutosh Das' => 'ashutosh@falcon',
            'Snigdha Samir Mohapatra' => 'snigdha@falcon',
            'Chirag Bansal' => 'chirag@falcon',
            'Niranjan Mishra' => 'niranjan@falcon',
            'Biranchi Narayan Biswal' => 'biranchi@falcon',
            'Umesh Mohapatra' => 'umesh@falcon',
            'Susmita Dutta' => 'susmita@falcon',
            'Sunil Dora' => 'sunil@falcon',
            'Syed Zubenoor Ali' => 'syed@falcon',
        ];

        foreach ($mapping as $name => $username) {
            $user = User::where('name', $name)->first();
            if ($user) {
                $user->update([
                    'username' => $username,
                    'email' => null,
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
