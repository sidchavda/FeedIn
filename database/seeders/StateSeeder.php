<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;
use App\Models\Country;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            // India - ID: 1
            ['name' => 'Andhra Pradesh', 'country_code' => 'IN', 'state_code' => 'AP'],
            ['name' => 'Arunachal Pradesh', 'country_code' => 'IN', 'state_code' => 'AR'],
            ['name' => 'Assam', 'country_code' => 'IN', 'state_code' => 'AS'],
            ['name' => 'Bihar', 'country_code' => 'IN', 'state_code' => 'BR'],
            ['name' => 'Chhattisgarh', 'country_code' => 'IN', 'state_code' => 'CG'],
            ['name' => 'Goa', 'country_code' => 'IN', 'state_code' => 'GA'],
            ['name' => 'Gujarat', 'country_code' => 'IN', 'state_code' => 'GJ'],
            ['name' => 'Haryana', 'country_code' => 'IN', 'state_code' => 'HR'],
            ['name' => 'Himachal Pradesh', 'country_code' => 'IN', 'state_code' => 'HP'],
            ['name' => 'Jharkhand', 'country_code' => 'IN', 'state_code' => 'JH'],
            ['name' => 'Karnataka', 'country_code' => 'IN', 'state_code' => 'KA'],
            ['name' => 'Kerala', 'country_code' => 'IN', 'state_code' => 'KL'],
            ['name' => 'Madhya Pradesh', 'country_code' => 'IN', 'state_code' => 'MP'],
            ['name' => 'Maharashtra', 'country_code' => 'IN', 'state_code' => 'MH'],
            ['name' => 'Manipur', 'country_code' => 'IN', 'state_code' => 'MN'],
            ['name' => 'Meghalaya', 'country_code' => 'IN', 'state_code' => 'ML'],
            ['name' => 'Mizoram', 'country_code' => 'IN', 'state_code' => 'MZ'],
            ['name' => 'Nagaland', 'country_code' => 'IN', 'state_code' => 'NL'],
            ['name' => 'Odisha', 'country_code' => 'IN', 'state_code' => 'OD'],
            ['name' => 'Punjab', 'country_code' => 'IN', 'state_code' => 'PB'],
            ['name' => 'Rajasthan', 'country_code' => 'IN', 'state_code' => 'RJ'],
            ['name' => 'Sikkim', 'country_code' => 'IN', 'state_code' => 'SK'],
            ['name' => 'Tamil Nadu', 'country_code' => 'IN', 'state_code' => 'TN'],
            ['name' => 'Telangana', 'country_code' => 'IN', 'state_code' => 'TS'],
            ['name' => 'Tripura', 'country_code' => 'IN', 'state_code' => 'TR'],
            ['name' => 'Uttar Pradesh', 'country_code' => 'IN', 'state_code' => 'UP'],
            ['name' => 'Uttarakhand', 'country_code' => 'IN', 'state_code' => 'UK'],
            ['name' => 'West Bengal', 'country_code' => 'IN', 'state_code' => 'WB'],
            ['name' => 'Andaman and Nicobar Islands', 'country_code' => 'IN', 'state_code' => 'AN'],
            ['name' => 'Chandigarh', 'country_code' => 'IN', 'state_code' => 'CH'],
            ['name' => 'Dadra and Nagar Haveli and Daman and Diu', 'country_code' => 'IN', 'state_code' => 'DN'],
            ['name' => 'Delhi', 'country_code' => 'IN', 'state_code' => 'DL'],
            ['name' => 'Jammu and Kashmir', 'country_code' => 'IN', 'state_code' => 'JK'],
            ['name' => 'Ladakh', 'country_code' => 'IN', 'state_code' => 'LA'],
            ['name' => 'Lakshadweep', 'country_code' => 'IN', 'state_code' => 'LD'],
            ['name' => 'Puducherry', 'country_code' => 'IN', 'state_code' => 'PY'],

            // United States - ID: 2
            ['name' => 'Alabama', 'country_code' => 'US', 'state_code' => 'AL'],
            ['name' => 'Alaska', 'country_code' => 'US', 'state_code' => 'AK'],
            ['name' => 'Arizona', 'country_code' => 'US', 'state_code' => 'AZ'],
            ['name' => 'Arkansas', 'country_code' => 'US', 'state_code' => 'AR'],
            ['name' => 'California', 'country_code' => 'US', 'state_code' => 'CA'],
            ['name' => 'Colorado', 'country_code' => 'US', 'state_code' => 'CO'],
            ['name' => 'Connecticut', 'country_code' => 'US', 'state_code' => 'CT'],
            ['name' => 'Delaware', 'country_code' => 'US', 'state_code' => 'DE'],
            ['name' => 'Florida', 'country_code' => 'US', 'state_code' => 'FL'],
            ['name' => 'Georgia', 'country_code' => 'US', 'state_code' => 'GA'],
            ['name' => 'Hawaii', 'country_code' => 'US', 'state_code' => 'HI'],
            ['name' => 'Idaho', 'country_code' => 'US', 'state_code' => 'ID'],
            ['name' => 'Illinois', 'country_code' => 'US', 'state_code' => 'IL'],
            ['name' => 'Indiana', 'country_code' => 'US', 'state_code' => 'IN'],
            ['name' => 'Iowa', 'country_code' => 'US', 'state_code' => 'IA'],
            ['name' => 'Kansas', 'country_code' => 'US', 'state_code' => 'KS'],
            ['name' => 'Kentucky', 'country_code' => 'US', 'state_code' => 'KY'],
            ['name' => 'Louisiana', 'country_code' => 'US', 'state_code' => 'LA'],
            ['name' => 'Maine', 'country_code' => 'US', 'state_code' => 'ME'],
            ['name' => 'Maryland', 'country_code' => 'US', 'state_code' => 'MD'],
            ['name' => 'Massachusetts', 'country_code' => 'US', 'state_code' => 'MA'],
            ['name' => 'Michigan', 'country_code' => 'US', 'state_code' => 'MI'],
            ['name' => 'Minnesota', 'country_code' => 'US', 'state_code' => 'MN'],
            ['name' => 'Mississippi', 'country_code' => 'US', 'state_code' => 'MS'],
            ['name' => 'Missouri', 'country_code' => 'US', 'state_code' => 'MO'],
            ['name' => 'Montana', 'country_code' => 'US', 'state_code' => 'MT'],
            ['name' => 'Nebraska', 'country_code' => 'US', 'state_code' => 'NE'],
            ['name' => 'Nevada', 'country_code' => 'US', 'state_code' => 'NV'],
            ['name' => 'New Hampshire', 'country_code' => 'US', 'state_code' => 'NH'],
            ['name' => 'New Jersey', 'country_code' => 'US', 'state_code' => 'NJ'],
            ['name' => 'New Mexico', 'country_code' => 'US', 'state_code' => 'NM'],
            ['name' => 'New York', 'country_code' => 'US', 'state_code' => 'NY'],
            ['name' => 'North Carolina', 'country_code' => 'US', 'state_code' => 'NC'],
            ['name' => 'North Dakota', 'country_code' => 'US', 'state_code' => 'ND'],
            ['name' => 'Ohio', 'country_code' => 'US', 'state_code' => 'OH'],
            ['name' => 'Oklahoma', 'country_code' => 'US', 'state_code' => 'OK'],
            ['name' => 'Oregon', 'country_code' => 'US', 'state_code' => 'OR'],
            ['name' => 'Pennsylvania', 'country_code' => 'US', 'state_code' => 'PA'],
            ['name' => 'Rhode Island', 'country_code' => 'US', 'state_code' => 'RI'],
            ['name' => 'South Carolina', 'country_code' => 'US', 'state_code' => 'SC'],
            ['name' => 'South Dakota', 'country_code' => 'US', 'state_code' => 'SD'],
            ['name' => 'Tennessee', 'country_code' => 'US', 'state_code' => 'TN'],
            ['name' => 'Texas', 'country_code' => 'US', 'state_code' => 'TX'],
            ['name' => 'Utah', 'country_code' => 'US', 'state_code' => 'UT'],
            ['name' => 'Vermont', 'country_code' => 'US', 'state_code' => 'VT'],
            ['name' => 'Virginia', 'country_code' => 'US', 'state_code' => 'VA'],
            ['name' => 'Washington', 'country_code' => 'US', 'state_code' => 'WA'],
            ['name' => 'West Virginia', 'country_code' => 'US', 'state_code' => 'WV'],
            ['name' => 'Wisconsin', 'country_code' => 'US', 'state_code' => 'WI'],
            ['name' => 'Wyoming', 'country_code' => 'US', 'state_code' => 'WY'],
            ['name' => 'Washington DC', 'country_code' => 'US', 'state_code' => 'DC'],

            // United Kingdom - ID: 3
            ['name' => 'England', 'country_code' => 'GB', 'state_code' => 'ENG'],
            ['name' => 'Scotland', 'country_code' => 'GB', 'state_code' => 'SCT'],
            ['name' => 'Wales', 'country_code' => 'GB', 'state_code' => 'WLS'],
            ['name' => 'Northern Ireland', 'country_code' => 'GB', 'state_code' => 'NIR'],

            // Canada - ID: 4
            ['name' => 'Alberta', 'country_code' => 'CA', 'state_code' => 'AB'],
            ['name' => 'British Columbia', 'country_code' => 'CA', 'state_code' => 'BC'],
            ['name' => 'Manitoba', 'country_code' => 'CA', 'state_code' => 'MB'],
            ['name' => 'New Brunswick', 'country_code' => 'CA', 'state_code' => 'NB'],
            ['name' => 'Newfoundland and Labrador', 'country_code' => 'CA', 'state_code' => 'NL'],
            ['name' => 'Nova Scotia', 'country_code' => 'CA', 'state_code' => 'NS'],
            ['name' => 'Ontario', 'country_code' => 'CA', 'state_code' => 'ON'],
            ['name' => 'Prince Edward Island', 'country_code' => 'CA', 'state_code' => 'PE'],
            ['name' => 'Quebec', 'country_code' => 'CA', 'state_code' => 'QC'],
            ['name' => 'Saskatchewan', 'country_code' => 'CA', 'state_code' => 'SK'],

            // Australia - ID: 5
            ['name' => 'New South Wales', 'country_code' => 'AU', 'state_code' => 'NSW'],
            ['name' => 'Victoria', 'country_code' => 'AU', 'state_code' => 'VIC'],
            ['name' => 'Queensland', 'country_code' => 'AU', 'state_code' => 'QLD'],
            ['name' => 'Western Australia', 'country_code' => 'AU', 'state_code' => 'WA'],
            ['name' => 'South Australia', 'country_code' => 'AU', 'state_code' => 'SA'],
            ['name' => 'Tasmania', 'country_code' => 'AU', 'state_code' => 'TAS'],
            ['name' => 'Australian Capital Territory', 'country_code' => 'AU', 'state_code' => 'ACT'],
            ['name' => 'Northern Territory', 'country_code' => 'AU', 'state_code' => 'NT'],

            // Germany - ID: 6
            ['name' => 'Baden-Württemberg', 'country_code' => 'DE', 'state_code' => 'BW'],
            ['name' => 'Bavaria', 'country_code' => 'DE', 'state_code' => 'BY'],
            ['name' => 'Berlin', 'country_code' => 'DE', 'state_code' => 'BE'],
            ['name' => 'Brandenburg', 'country_code' => 'DE', 'state_code' => 'BB'],
            ['name' => 'Bremen', 'country_code' => 'DE', 'state_code' => 'HB'],
            ['name' => 'Hamburg', 'country_code' => 'DE', 'state_code' => 'HH'],
            ['name' => 'Hesse', 'country_code' => 'DE', 'state_code' => 'HE'],
            ['name' => 'Lower Saxony', 'country_code' => 'DE', 'state_code' => 'NI'],
            ['name' => 'Mecklenburg-Vorpommern', 'country_code' => 'DE', 'state_code' => 'MV'],
            ['name' => 'North Rhine-Westphalia', 'country_code' => 'DE', 'state_code' => 'NW'],
            ['name' => 'Rhineland-Palatinate', 'country_code' => 'DE', 'state_code' => 'RP'],
            ['name' => 'Saarland', 'country_code' => 'DE', 'state_code' => 'SL'],
            ['name' => 'Saxony', 'country_code' => 'DE', 'state_code' => 'SN'],
            ['name' => 'Saxony-Anhalt', 'country_code' => 'DE', 'state_code' => 'ST'],
            ['name' => 'Schleswig-Holstein', 'country_code' => 'DE', 'state_code' => 'SH'],
            ['name' => 'Thuringia', 'country_code' => 'DE', 'state_code' => 'TH'],

            // France - ID: 7
            ['name' => 'Auvergne-Rhône-Alpes', 'country_code' => 'FR', 'state_code' => 'ARA'],
            ['name' => 'Bourgogne-Franche-Comté', 'country_code' => 'FR', 'state_code' => 'BFC'],
            ['name' => 'Brittany', 'country_code' => 'FR', 'state_code' => 'BRE'],
            ['name' => 'Centre-Val de Loire', 'country_code' => 'FR', 'state_code' => 'CVL'],
            ['name' => 'Corsica', 'country_code' => 'FR', 'state_code' => 'COR'],
            ['name' => 'Grand Est', 'country_code' => 'FR', 'state_code' => 'GES'],
            ['name' => 'Hauts-de-France', 'country_code' => 'FR', 'state_code' => 'HDF'],
            ['name' => 'Île-de-France', 'country_code' => 'FR', 'state_code' => 'IDF'],
            ['name' => 'Normandy', 'country_code' => 'FR', 'state_code' => 'NOR'],
            ['name' => 'Nouvelle-Aquitaine', 'country_code' => 'FR', 'state_code' => 'NAQ'],
            ['name' => 'Occitanie', 'country_code' => 'FR', 'state_code' => 'OCC'],
            ['name' => 'Pays de la Loire', 'country_code' => 'FR', 'state_code' => 'PDL'],
            ['name' => 'Provence-Alpes-Côte d\'Azur', 'country_code' => 'FR', 'state_code' => 'PAC'],

            // China - ID: 8
            ['name' => 'Beijing', 'country_code' => 'CN', 'state_code' => 'BJ'],
            ['name' => 'Shanghai', 'country_code' => 'CN', 'state_code' => 'SH'],
            ['name' => 'Guangdong', 'country_code' => 'CN', 'state_code' => 'GD'],
            ['name' => 'Jiangsu', 'country_code' => 'CN', 'state_code' => 'JS'],
            ['name' => 'Zhejiang', 'country_code' => 'CN', 'state_code' => 'ZJ'],

            // Japan - ID: 9
            ['name' => 'Tokyo', 'country_code' => 'JP', 'state_code' => '13'],
            ['name' => 'Osaka', 'country_code' => 'JP', 'state_code' => '27'],
            ['name' => 'Aichi', 'country_code' => 'JP', 'state_code' => '23'],
            ['name' => 'Hokkaido', 'country_code' => 'JP', 'state_code' => '01'],
            ['name' => 'Fukuoka', 'country_code' => 'JP', 'state_code' => '40'],

            // Brazil - ID: 10
            ['name' => 'São Paulo', 'country_code' => 'BR', 'state_code' => 'SP'],
            ['name' => 'Rio de Janeiro', 'country_code' => 'BR', 'state_code' => 'RJ'],
            ['name' => 'Minas Gerais', 'country_code' => 'BR', 'state_code' => 'MG'],
            ['name' => 'Bahia', 'country_code' => 'BR', 'state_code' => 'BA'],

            // Mexico - ID: 13
            ['name' => 'Jalisco', 'country_code' => 'MX', 'state_code' => 'JAL'],
            ['name' => 'Nuevo León', 'country_code' => 'MX', 'state_code' => 'NLE'],
            ['name' => 'Puebla', 'country_code' => 'MX', 'state_code' => 'PUE'],
            ['name' => 'Guanajuato', 'country_code' => 'MX', 'state_code' => 'GUA'],

            // Italy - ID: 14
            ['name' => 'Lombardy', 'country_code' => 'IT', 'state_code' => 'LOM'],
            ['name' => 'Lazio', 'country_code' => 'IT', 'state_code' => 'LAZ'],
            ['name' => 'Campania', 'country_code' => 'IT', 'state_code' => 'CAM'],
            ['name' => 'Sicily', 'country_code' => 'IT', 'state_code' => 'SIC'],

            // Spain - ID: 15
            ['name' => 'Catalonia', 'country_code' => 'ES', 'state_code' => 'CT'],
            ['name' => 'Madrid', 'country_code' => 'ES', 'state_code' => 'MD'],
            ['name' => 'Andalusia', 'country_code' => 'ES', 'state_code' => 'AN'],
            ['name' => 'Valencia', 'country_code' => 'ES', 'state_code' => 'VC'],

            // Netherlands - ID: 17
            ['name' => 'North Holland', 'country_code' => 'NL', 'state_code' => 'NH'],
            ['name' => 'South Holland', 'country_code' => 'NL', 'state_code' => 'ZH'],
            ['name' => 'Utrecht', 'country_code' => 'NL', 'state_code' => 'UT'],

            // Switzerland - ID: 18
            ['name' => 'Zurich', 'country_code' => 'CH', 'state_code' => 'ZH'],
            ['name' => 'Geneva', 'country_code' => 'CH', 'state_code' => 'GE'],
            ['name' => 'Bern', 'country_code' => 'CH', 'state_code' => 'BE'],

            // UAE - ID: 20
            ['name' => 'Dubai', 'country_code' => 'AE', 'state_code' => 'DU'],
            ['name' => 'Abu Dhabi', 'country_code' => 'AE', 'state_code' => 'AZ'],
            ['name' => 'Sharjah', 'country_code' => 'AE', 'state_code' => 'SH'],

            // Pakistan - ID: 21
            ['name' => 'Punjab', 'country_code' => 'PK', 'state_code' => 'PB'],
            ['name' => 'Sindh', 'country_code' => 'PK', 'state_code' => 'SD'],
            ['name' => 'Khyber Pakhtunkhwa', 'country_code' => 'PK', 'state_code' => 'KP'],
            ['name' => 'Balochistan', 'country_code' => 'PK', 'state_code' => 'BA'],

            // Bangladesh - ID: 22
            ['name' => 'Dhaka Division', 'country_code' => 'BD', 'state_code' => 'DH'],
            ['name' => 'Chittagong Division', 'country_code' => 'BD', 'state_code' => 'CG'],
            ['name' => 'Khulna Division', 'country_code' => 'BD', 'state_code' => 'KU'],
            ['name' => 'Rajshahi Division', 'country_code' => 'BD', 'state_code' => 'RJ'],

            // Sri Lanka - ID: 23
            ['name' => 'Western Province', 'country_code' => 'LK', 'state_code' => 'WP'],
            ['name' => 'Central Province', 'country_code' => 'LK', 'state_code' => 'CP'],
            ['name' => 'Southern Province', 'country_code' => 'LK', 'state_code' => 'SP'],

            // Nepal - ID: 24
            ['name' => 'Bagmati Province', 'country_code' => 'NP', 'state_code' => 'BA'],
            ['name' => 'Lumbini Province', 'country_code' => 'NP', 'state_code' => 'LU'],
            ['name' => 'Koshi Province', 'country_code' => 'NP', 'state_code' => 'KO'],

            // Malaysia - ID: 25
            ['name' => 'Kuala Lumpur', 'country_code' => 'MY', 'state_code' => 'KL'],
            ['name' => 'Selangor', 'country_code' => 'MY', 'state_code' => 'SEL'],
            ['name' => 'Penang', 'country_code' => 'MY', 'state_code' => 'PEN'],

            // Indonesia - ID: 26
            ['name' => 'Jakarta', 'country_code' => 'ID', 'state_code' => 'JK'],
            ['name' => 'West Java', 'country_code' => 'ID', 'state_code' => 'JB'],
            ['name' => 'East Java', 'country_code' => 'ID', 'state_code' => 'JI'],
            ['name' => 'Bali', 'country_code' => 'ID', 'state_code' => 'BA'],

            // Thailand - ID: 27
            ['name' => 'Bangkok', 'country_code' => 'TH', 'state_code' => '10'],
            ['name' => 'Chiang Mai', 'country_code' => 'TH', 'state_code' => '50'],
            ['name' => 'Phuket', 'country_code' => 'TH', 'state_code' => '83'],

            // Philippines - ID: 28
            ['name' => 'Metro Manila', 'country_code' => 'PH', 'state_code' => 'NCR'],
            ['name' => 'Cebu', 'country_code' => 'PH', 'state_code' => 'CEB'],
            ['name' => 'Davao', 'country_code' => 'PH', 'state_code' => 'DAS'],

            // Vietnam - ID: 29
            ['name' => 'Hanoi', 'country_code' => 'VN', 'state_code' => 'HN'],
            ['name' => 'Ho Chi Minh City', 'country_code' => 'VN', 'state_code' => 'SG'],
            ['name' => 'Da Nang', 'country_code' => 'VN', 'state_code' => 'DN'],

            // Saudi Arabia - ID: 30
            ['name' => 'Riyadh Province', 'country_code' => 'SA', 'state_code' => '01'],
            ['name' => 'Makkah Province', 'country_code' => 'SA', 'state_code' => '02'],
            ['name' => 'Eastern Province', 'country_code' => 'SA', 'state_code' => '04'],

            // Turkey - ID: 31
            ['name' => 'Istanbul', 'country_code' => 'TR', 'state_code' => '34'],
            ['name' => 'Ankara', 'country_code' => 'TR', 'state_code' => '06'],
            ['name' => 'Izmir', 'country_code' => 'TR', 'state_code' => '35'],

            // South Korea - ID: 32
            ['name' => 'Seoul', 'country_code' => 'KR', 'state_code' => '11'],
            ['name' => 'Busan', 'country_code' => 'KR', 'state_code' => '26'],
            ['name' => 'Incheon', 'country_code' => 'KR', 'state_code' => '28'],

            // New Zealand - ID: 33
            ['name' => 'Auckland', 'country_code' => 'NZ', 'state_code' => 'AUK'],
            ['name' => 'Wellington', 'country_code' => 'NZ', 'state_code' => 'WGN'],
            ['name' => 'Canterbury', 'country_code' => 'NZ', 'state_code' => 'CAN'],

            // Egypt - ID: 34
            ['name' => 'Cairo Governorate', 'country_code' => 'EG', 'state_code' => 'C'],
            ['name' => 'Alexandria Governorate', 'country_code' => 'EG', 'state_code' => 'ALX'],

            // Nigeria - ID: 35
            ['name' => 'Lagos', 'country_code' => 'NG', 'state_code' => 'LA'],
            ['name' => 'Kano', 'country_code' => 'NG', 'state_code' => 'KN'],
            ['name' => 'Abuja', 'country_code' => 'NG', 'state_code' => 'FC'],

            // Kenya - ID: 36
            ['name' => 'Nairobi', 'country_code' => 'KE', 'state_code' => '110'],
            ['name' => 'Mombasa', 'country_code' => 'KE', 'state_code' => '1'],

            // South Africa - ID: 12
            ['name' => 'Gauteng', 'country_code' => 'ZA', 'state_code' => 'GP'],
            ['name' => 'Western Cape', 'country_code' => 'ZA', 'state_code' => 'WC'],
            ['name' => 'KwaZulu-Natal', 'country_code' => 'ZA', 'state_code' => 'KZN'],

            // Russia - ID: 11
            ['name' => 'Moscow', 'country_code' => 'RU', 'state_code' => 'MOW'],
            ['name' => 'Saint Petersburg', 'country_code' => 'RU', 'state_code' => 'SPE'],
            ['name' => 'Sverdlovsk Oblast', 'country_code' => 'RU', 'state_code' => 'SVE'],

            // Argentina - ID: 16
            ['name' => 'Buenos Aires', 'country_code' => 'AR', 'state_code' => 'B'],
            ['name' => 'Córdoba', 'country_code' => 'AR', 'state_code' => 'X'],

            // Sweden - ID: 45
            ['name' => 'Stockholm County', 'country_code' => 'SE', 'state_code' => 'AB'],
            ['name' => 'Västra Götaland County', 'country_code' => 'SE', 'state_code' => 'O'],

            // Norway - ID: 46
            ['name' => 'Oslo', 'country_code' => 'NO', 'state_code' => '03'],
            ['name' => 'Viken', 'country_code' => 'NO', 'state_code' => '30'],

            // Poland - ID: 49
            ['name' => 'Masovian Voivodeship', 'country_code' => 'PL', 'state_code' => '14'],
            ['name' => 'Lesser Poland Voivodeship', 'country_code' => 'PL', 'state_code' => '12'],

            // Portugal - ID: 51
            ['name' => 'Lisbon', 'country_code' => 'PT', 'state_code' => '11'],
            ['name' => 'Porto', 'country_code' => 'PT', 'state_code' => '13'],

            // Greece - ID: 52
            ['name' => 'Attica', 'country_code' => 'GR', 'state_code' => 'I'],
            ['name' => 'Central Macedonia', 'country_code' => 'GR', 'state_code' => 'B'],

            // Czech Republic - ID: 53
            ['name' => 'Prague', 'country_code' => 'CZ', 'state_code' => '10'],
            ['name' => 'Central Bohemian', 'country_code' => 'CZ', 'state_code' => '20'],

            // Austria - ID: 55
            ['name' => 'Vienna', 'country_code' => 'AT', 'state_code' => '9'],
            ['name' => 'Lower Austria', 'country_code' => 'AT', 'state_code' => '3'],

            // Belgium - ID: 56
            ['name' => 'Brussels', 'country_code' => 'BE', 'state_code' => 'BRU'],
            ['name' => 'Flanders', 'country_code' => 'BE', 'state_code' => 'VLG'],

            // Finland - ID: 57
            ['name' => 'Uusimaa', 'country_code' => 'FI', 'state_code' => '18'],
            ['name' => 'Southwest Finland', 'country_code' => 'FI', 'state_code' => '19'],

            // Ireland - ID: 58
            ['name' => 'Leinster', 'country_code' => 'IE', 'state_code' => 'L'],
            ['name' => 'Munster', 'country_code' => 'IE', 'state_code' => 'M'],

            // Qatar - ID: 59
            ['name' => 'Doha', 'country_code' => 'QA', 'state_code' => 'DA'],
            ['name' => 'Al Rayyan', 'country_code' => 'QA', 'state_code' => 'RA'],

            // Kuwait - ID: 60
            ['name' => 'Al Asimah', 'country_code' => 'KW', 'state_code' => 'KU'],
            ['name' => 'Hawalli', 'country_code' => 'KW', 'state_code' => 'HA'],

            // Oman - ID: 61
            ['name' => 'Muscat Governorate', 'country_code' => 'OM', 'state_code' => 'MA'],
            ['name' => 'Dhofar Governorate', 'country_code' => 'OM', 'state_code' => 'ZU'],

            // Jordan - ID: 63
            ['name' => 'Amman Governorate', 'country_code' => 'JO', 'state_code' => 'AM'],
            ['name' => 'Irbid Governorate', 'country_code' => 'JO', 'state_code' => 'IR'],

            // Morocco - ID: 65
            ['name' => 'Casablanca-Settat', 'country_code' => 'MA', 'state_code' => 'CS'],
            ['name' => 'Marrakesh-Safi', 'country_code' => 'MA', 'state_code' => 'MS'],

            // Algeria - ID: 66
            ['name' => 'Algiers', 'country_code' => 'DZ', 'state_code' => '16'],
            ['name' => 'Oran', 'country_code' => 'DZ', 'state_code' => '31'],

            // Ghana - ID: 68
            ['name' => 'Greater Accra', 'country_code' => 'GH', 'state_code' => 'AA'],
            ['name' => 'Ashanti', 'country_code' => 'GH', 'state_code' => 'AH'],

            // Tanzania - ID: 69
            ['name' => 'Dar es Salaam', 'country_code' => 'TZ', 'state_code' => '02'],
            ['name' => 'Arusha', 'country_code' => 'TZ', 'state_code' => '01'],

            // Uganda - ID: 70
            ['name' => 'Central Region', 'country_code' => 'UG', 'state_code' => 'C'],
            ['name' => 'Western Region', 'country_code' => 'UG', 'state_code' => 'W'],

            // Zimbabwe - ID: 71
            ['name' => 'Harare', 'country_code' => 'ZW', 'state_code' => 'HA'],
            ['name' => 'Bulawayo', 'country_code' => 'ZW', 'state_code' => 'BU'],

            // Zambia - ID: 72
            ['name' => 'Lusaka Province', 'country_code' => 'ZM', 'state_code' => '09'],
            ['name' => 'Copperbelt Province', 'country_code' => 'ZM', 'state_code' => '08'],
        ];

        foreach ($states as $state) {
            $country = Country::where('code', $state['country_code'])->first();
            if ($country) {
                State::create([
                    'name' => $state['name'],
                    'country_id' => $country->id,
                    'state_code' => $state['state_code'],
                ]);
            }
        }
    }
}
