<?php
namespace App\Imports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Exception;
class LeadsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected int $categoryId;
    protected int $batchSize = 1000; // Define the batch size

    /**
    * Constructor to initialize the category ID.
    *
    * @param int $categoryId
    */
    public function __construct(int $categoryId)
    {
      $this->categoryId = $categoryId;
    }

    /**
    * @param Collection $rows
    */
    public function collection(Collection $rows)
    {
        $data = [];
        $rowCount = 0;
        try {
            Log::info("start process with $this->categoryId.");
            foreach ($rows as $row) {
                    $data[] = [
                        'lead_id' => $row['lead_id'],
                        'category_id' => $this->categoryId,
                        'first_name' => $row['first_name'],
                        'last_name' => $row['last_name'],
                        'phone_home' => $row['phone_home'],
                        'phone_cell' => $row['phone_cell'],
                        'phone_ext' => $row['phone_ext'],
                        'address' => $row['address'],
                        'address2' => $row['address2'],
                        'city' => $row['city'],
                        'state' => $row['state'],
                        'zip_code' => $row['zip_code'],
                        'country' => $row['country'],
                        'dob' => $row['date_of_birth'],
                        'email' => $row['e_mail_address'],
                        'gender' => $row['gender'],
                        'marital_status' => $row['marital_status'],
                        'income' => $row['income'],
                        'website' => $row['website']
                    ];


                    if (count($data) >= $this->batchSize) {
    //                    $rowCount += count($data);
    //                    Log::info("Inserted $rowCount rows into the database (cat = $this->categoryId)");
                        Lead::insert($data);
                          $data = [];
                    }
            }

            if (!empty($data)) {
                Lead::insert($data);
                Log::info("Inserted rows into the database (final batch).");
            }
        } catch (Exception $e) {
            Log::error('An error occurred: ' . $e->getMessage());
        }
        return "done";
    }

    /**
    * Define the chunk size for reading the file.
    *
    * @return int
    */
    public function chunkSize(): int
    {
        return 1000; // Process 1000 rows at a time
    }
}
