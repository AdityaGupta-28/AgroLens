<?php

namespace Database\Seeders;

use App\Enums\CropSeason;
use App\Enums\IrrigationSourceType;
use App\Enums\LandHoldingCategory;
use App\Enums\RegionType;
use App\Enums\UserRole;
use App\Models\Crop;
use App\Models\CropPattern;
use App\Models\Farmer;
use App\Models\IrrigationRecord;
use App\Models\LandHolding;
use App\Models\Region;
use App\Models\User;
use App\Models\Well;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class AgroLensPlatformSeeder extends Seeder
{
    /** @var array<string, list<string>> */
    private const REGIONAL_CROPS = [
        'Punjab' => ['Wheat', 'Rice (Paddy)', 'Mustard', 'Potato'],
        'Maharashtra' => ['Sugarcane', 'Grapes', 'Cotton', 'Maize'],
        'Tamil Nadu' => ['Rice (Paddy)', 'Sugarcane', 'Banana', 'Turmeric'],
        'Rajasthan' => ['Wheat', 'Cotton', 'Mustard', 'Sesame'],
        'Andhra Pradesh' => ['Rice (Paddy)', 'Groundnut', 'Mango', 'Sugarcane'],
        'Madhya Pradesh' => ['Soybean', 'Wheat', 'Chickpea', 'Cotton'],
        'Karnataka' => ['Banana', 'Cotton', 'Sugarcane', 'Rice (Paddy)'],
        'Gujarat' => ['Cotton', 'Groundnut', 'Sesame', 'Mustard'],
        'Uttar Pradesh' => ['Wheat', 'Rice (Paddy)', 'Potato', 'Sugarcane'],
        'West Bengal' => ['Rice (Paddy)', 'Potato', 'Mustard', 'Tea'],
        'Arunachal Pradesh' => ['Rice (Paddy)', 'Maize', 'Tea', 'Millet'],
        'Assam' => ['Rice (Paddy)', 'Tea', 'Mustard', 'Banana'],
        'Bihar' => ['Rice (Paddy)', 'Wheat', 'Maize', 'Sugarcane'],
        'Chhattisgarh' => ['Rice (Paddy)', 'Soybean', 'Turmeric', 'Maize'],
        'Goa' => ['Rice (Paddy)', 'Coconut', 'Cashew', 'Banana'],
        'Haryana' => ['Wheat', 'Rice (Paddy)', 'Cotton', 'Mustard'],
        'Himachal Pradesh' => ['Apple', 'Potato', 'Maize', 'Wheat'],
        'Jharkhand' => ['Rice (Paddy)', 'Maize', 'Soybean', 'Sugarcane'],
        'Kerala' => ['Banana', 'Coconut', 'Pepper', 'Ginger'],
        'Manipur' => ['Rice (Paddy)', 'Maize', 'Banana', 'Tea'],
        'Meghalaya' => ['Rice (Paddy)', 'Potato', 'Ginger', 'Arecanut'],
        'Mizoram' => ['Rice (Paddy)', 'Maize', 'Banana', 'Ginger'],
        'Nagaland' => ['Rice (Paddy)', 'Maize', 'Tea', 'Ginger'],
        'Odisha' => ['Rice (Paddy)', 'Sugarcane', 'Banana', 'Groundnut'],
        'Sikkim' => ['Rice (Paddy)', 'Potato', 'Apple', 'Tea'],
        'Telangana' => ['Rice (Paddy)', 'Cotton', 'Maize', 'Sugarcane'],
        'Tripura' => ['Rice (Paddy)', 'Banana', 'Tea', 'Ginger'],
        'Uttarakhand' => ['Rice (Paddy)', 'Wheat', 'Apple', 'Potato'],
    ];

    public function run(): void
    {
        $india = Region::firstOrCreate([
            'name' => 'India',
            'type' => RegionType::Country,
        ], [
            'state' => 'India',
            'code' => 'IN',
            'population' => 1400000000,
        ]);

        $stateMeta = [
            'Andhra Pradesh' => ['lat' => 15.9129, 'lng' => 79.7400, 'zone' => 'Krishna-Godavari Zone'],
            'Arunachal Pradesh' => ['lat' => 28.2180, 'lng' => 94.7278, 'zone' => 'Himalayan Hill Zone'],
            'Assam' => ['lat' => 26.2006, 'lng' => 92.9376, 'zone' => 'Brahmaputra Valley'],
            'Bihar' => ['lat' => 25.0961, 'lng' => 85.3131, 'zone' => 'Indo-Gangetic Plain'],
            'Chhattisgarh' => ['lat' => 21.2787, 'lng' => 81.8661, 'zone' => 'Chhattisgarh Plains'],
            'Goa' => ['lat' => 15.2993, 'lng' => 74.1240, 'zone' => 'Western Coastal Plains'],
            'Gujarat' => ['lat' => 22.2587, 'lng' => 71.1924, 'zone' => 'Saurashtra Plains'],
            'Haryana' => ['lat' => 29.0588, 'lng' => 76.0856, 'zone' => 'Indo-Gangetic Plain'],
            'Himachal Pradesh' => ['lat' => 31.1048, 'lng' => 77.1734, 'zone' => 'Sub-Himalayan Region'],
            'Jharkhand' => ['lat' => 23.6102, 'lng' => 85.2799, 'zone' => 'Chota Nagpur Plateau'],
            'Karnataka' => ['lat' => 15.3173, 'lng' => 75.7139, 'zone' => 'Deccan Plateau'],
            'Kerala' => ['lat' => 10.8505, 'lng' => 76.2711, 'zone' => 'Western Coastal Plains'],
            'Madhya Pradesh' => ['lat' => 22.9734, 'lng' => 78.6569, 'zone' => 'Malwa & Vindhya Plateau'],
            'Maharashtra' => ['lat' => 19.7515, 'lng' => 75.7139, 'zone' => 'Deccan Plateau'],
            'Manipur' => ['lat' => 24.6637, 'lng' => 93.9063, 'zone' => 'North Eastern Hills'],
            'Meghalaya' => ['lat' => 25.4670, 'lng' => 91.3662, 'zone' => 'North Eastern Hills'],
            'Mizoram' => ['lat' => 23.1645, 'lng' => 92.9376, 'zone' => 'North Eastern Hills'],
            'Nagaland' => ['lat' => 26.1584, 'lng' => 94.5624, 'zone' => 'North Eastern Hills'],
            'Odisha' => ['lat' => 20.9517, 'lng' => 85.0985, 'zone' => 'Eastern Coastal Plains'],
            'Punjab' => ['lat' => 31.1471, 'lng' => 75.3412, 'zone' => 'Indo-Gangetic Plain'],
            'Rajasthan' => ['lat' => 27.0238, 'lng' => 74.2179, 'zone' => 'Thar Desert & Aravalli'],
            'Sikkim' => ['lat' => 27.5330, 'lng' => 88.5122, 'zone' => 'Himalayan Foothills'],
            'Tamil Nadu' => ['lat' => 11.1271, 'lng' => 78.6569, 'zone' => 'Cauvery Basin'],
            'Telangana' => ['lat' => 18.1124, 'lng' => 79.0193, 'zone' => 'Deccan Plateau'],
            'Tripura' => ['lat' => 23.9408, 'lng' => 91.9882, 'zone' => 'North Eastern Plains'],
            'Uttar Pradesh' => ['lat' => 26.8467, 'lng' => 80.9462, 'zone' => 'Indo-Gangetic Plain'],
            'Uttarakhand' => ['lat' => 30.0668, 'lng' => 79.0193, 'zone' => 'Himalayan Foothills'],
            'West Bengal' => ['lat' => 22.9868, 'lng' => 87.8550, 'zone' => 'Ganges Delta'],
        ];

        $stateDistricts = [
            'Andhra Pradesh' => ['Anantapur', 'Chittoor', 'East Godavari', 'Guntur', 'Krishna', 'Kurnool', 'Prakasam', 'Srikakulam', 'Nellore', 'Visakhapatnam', 'Vizianagaram', 'West Godavari', 'YSR Kadapa', 'Parvathipuram Manyam', 'Alluri Sitharama Raju', 'Anakapalli', 'Kakinada', 'Konaseema', 'Eluru', 'NTR', 'Bapatla', 'Palnadu', 'Nandyal', 'Sri Sathya Sai', 'Annamayya', 'Tirupati'],
            'Arunachal Pradesh' => ['Tawang', 'West Kameng', 'East Kameng', 'Papum Pare', 'Kurung Kumey', 'Kra Daadi', 'Lower Subansiri', 'Upper Subansiri', 'West Siang', 'East Siang', 'Siang', 'Upper Siang', 'Lower Siang', 'Lower Dibang Valley', 'Dibang Valley', 'Anjaw', 'Lohit', 'Namsai', 'Changlang', 'Tirap', 'Longding', 'Kamle', 'Pakke Kessang', 'Lepa Rada', 'Shi Yomi', 'Keyi Panyor'],
            'Assam' => ['Baksa', 'Barpeta', 'Bongaigaon', 'Cachar', 'Charaideo', 'Chirang', 'Darrang', 'Dhemaji', 'Dhubri', 'Dibrugarh', 'Dima Hasao', 'Goalpara', 'Golaghat', 'Hailakandi', 'Jorhat', 'Kamrup', 'Kamrup Metropolitan', 'Karbi Anglong', 'Karimganj', 'Kokrajhar', 'Lakhimpur', 'Majuli', 'Morigaon', 'Nagaon', 'Nalbari', 'Sivasagar', 'Sonitpur', 'South Salmara-Mankachar', 'Tinsukia', 'Udalguri', 'West Karbi Anglong'],
            'Bihar' => ['Araria', 'Arwal', 'Aurangabad', 'Banka', 'Begusarai', 'Bhagalpur', 'Bhojpur', 'Buxar', 'Darbhanga', 'East Champaran', 'Gaya', 'Gopalganj', 'Jamui', 'Jehanabad', 'Kaimur', 'Katihar', 'Khagaria', 'Kishanganj', 'Lakhisarai', 'Madhepura', 'Madhubani', 'Munger', 'Muzaffarpur', 'Nalanda', 'Nawada', 'Patna', 'Purnia', 'Rohtas', 'Saharsa', 'Samastipur', 'Saran', 'Sheikhpura', 'Sheohar', 'Sitamarhi', 'Siwan', 'Supaul', 'Vaishali', 'West Champaran'],
            'Chhattisgarh' => ['Balod', 'Baloda Bazar', 'Balrampur', 'Bastar', 'Bemetara', 'Bijapur', 'Bilaspur', 'Dantewada', 'Dhamtari', 'Durg', 'Gariaband', 'Janjgir-Champa', 'Jashpur', 'Kabirdham', 'Kanker', 'Kondagaon', 'Korba', 'Koriya', 'Mahasamund', 'Mungeli', 'Narayanpur', 'Raigarh', 'Raipur', 'Rajnandgaon', 'Sukma', 'Surajpur', 'Surguja', 'Gaurela-Pendra-Marwahi', 'Manendragarh-Chirmiri-Bharatpur', 'Mohla-Manpur-Ambagarh Chowki', 'Sarangarh-Bilaigarh', 'Sakti', 'Khairagarh-Chhuikhadan-Gandai'],
            'Goa' => ['North Goa', 'South Goa'],
            'Gujarat' => ['Ahmedabad', 'Amreli', 'Anand', 'Aravalli', 'Banaskantha', 'Bharuch', 'Bhavnagar', 'Dahod', 'Dang', 'Devbhumi Dwarka', 'Gandhinagar', 'Gir Somnath', 'Jamnagar', 'Junagadh', 'Kheda', 'Kutch', 'Mahisagar', 'Mehsana', 'Morbi', 'Narmada', 'Navsari', 'Panchmahal', 'Patan', 'Porbandar', 'Rajkot', 'Sabarkantha', 'Surat', 'Surendranagar', 'Tapi', 'Vadodara', 'Valsad', 'Botad', 'Chhota Udepur'],
            'Haryana' => ['Ambala', 'Bhiwani', 'Charkhi Dadri', 'Faridabad', 'Fatehabad', 'Gurugram', 'Hisar', 'Jhajjar', 'Jind', 'Kaithal', 'Karnal', 'Kurukshetra', 'Mahendragarh', 'Nuh', 'Palwal', 'Panchkula', 'Panipat', 'Rewari', 'Rohtak', 'Sirsa', 'Sonipat', 'Yamunanagar'],
            'Himachal Pradesh' => ['Bilaspur', 'Chamba', 'Hamirpur', 'Kangra', 'Kinnaur', 'Kullu', 'Lahaul and Spiti', 'Mandi', 'Shimla', 'Sirmaur', 'Solan', 'Una'],
            'Jharkhand' => ['Bokaro', 'Chatra', 'Deoghar', 'Dhanbad', 'Dumka', 'East Singhbhum', 'Garhwa', 'Giridih', 'Godda', 'Gumla', 'Hazaribagh', 'Jamtara', 'Khunti', 'Koderma', 'Latehar', 'Lohardaga', 'Pakur', 'Palamu', 'Ramgarh', 'Ranchi', 'Sahibganj', 'Seraikela Kharsawan', 'Simdega', 'West Singhbhum'],
            'Karnataka' => ['Bagalkote', 'Ballari', 'Belagavi', 'Bengaluru Rural', 'Bengaluru Urban', 'Bidar', 'Chamarajanagar', 'Chikkaballapur', 'Chikkamagaluru', 'Chitradurga', 'Dakshina Kannada', 'Davanagere', 'Dharwad', 'Gadag', 'Hassan', 'Haveri', 'Kalaburagi', 'Kodagu', 'Kolar', 'Koppal', 'Mandya', 'Mysuru', 'Raichur', 'Ramanagara', 'Shivamogga', 'Tumakuru', 'Udupi', 'Uttara Kannada', 'Vijayapura', 'Yadgir', 'Vijayanagara'],
            'Kerala' => ['Alappuzha', 'Ernakulam', 'Idukki', 'Kannur', 'Kasaragod', 'Kollam', 'Kottayam', 'Kozhikode', 'Malappuram', 'Palakkad', 'Pathanamthitta', 'Thiruvananthapuram', 'Thrissur', 'Wayanad'],
            'Madhya Pradesh' => ['Agar Malwa', 'Alirajpur', 'Anuppur', 'Ashoknagar', 'Balaghat', 'Barwani', 'Betul', 'Bhind', 'Bhopal', 'Burhanpur', 'Chhatarpur', 'Chhindwara', 'Damoh', 'Datia', 'Dewas', 'Dhar', 'Dindori', 'Guna', 'Gwalior', 'Harda', 'Narmadapuram', 'Indore', 'Jabalpur', 'Jhabua', 'Katni', 'Khandwa', 'Khargone', 'Mandla', 'Mandsaur', 'Morena', 'Narsinghpur', 'Neemuch', 'Niwari', 'Panna', 'Raisen', 'Rajgarh', 'Ratlam', 'Rewa', 'Sagar', 'Satna', 'Seore', 'Seoni', 'Shahdol', 'Shajapur', 'Sheopur', 'Shivpuri', 'Sidhi', 'Singrauli', 'Tikamgarh', 'Ujjain', 'Umaria', 'Vidisha', 'Mauganj', 'Maihar', 'Pandhurna'],
            'Maharashtra' => ['Ahmednagar', 'Akola', 'Amravati', 'Chhatrapati Sambhajinagar', 'Bhandara', 'Buldhana', 'Chandrapur', 'Dhule', 'Gadchiroli', 'Gondia', 'Hingoli', 'Jalgaon', 'Jalna', 'Kolhapur', 'Latur', 'Mumbai City', 'Mumbai Suburban', 'Nagpur', 'Nanded', 'Nandurbar', 'Nashik', 'Osmanabad', 'Palghar', 'Parbhani', 'Pune', 'Raigad', 'Ratnagiri', 'Sangli', 'Satara', 'Sindhudurg', 'Solapur', 'Thane', 'Wardha', 'Washim', 'Yavatmal', 'Dharashiv'],
            'Manipur' => ['Bishnupur', 'Chandel', 'Churachandpur', 'Imphal East', 'Imphal West', 'Senapati', 'Tamenglong', 'Thoubal', 'Ukhrul', 'Noney', 'Kamjong', 'Kangpokpi', 'Tengnoupal', 'Pherzawl', 'Kakching', 'Jiribam'],
            'Meghalaya' => ['East Garo Hills', 'East Khasi Hills', 'Jaintia Hills', 'Ri Bhoi', 'South Garo Hills', 'West Garo Hills', 'West Khasi Hills', 'South West Khasi Hills', 'West Jaintia Hills', 'East Jaintia Hills', 'North Garo Hills', 'South West Garo Hills', 'Eastern West Khasi Hills'],
            'Mizoram' => ['Aizawl', 'Champhai', 'Kolasib', 'Lawngtlai', 'Lunglei', 'Mamit', 'Saiha', 'Serchhip', 'Hnahthial', 'Khawzawl', 'Saitual'],
            'Nagaland' => ['Dimapur', 'Kiphire', 'Kohima', 'Longleng', 'Mokokchung', 'Mon', 'Peren', 'Phek', 'Tuensang', 'Wokha', 'Zunheboto', 'Noklak', 'Shamator', 'Tseminyu', 'Niuland', 'Chumoukedima'],
            'Odisha' => ['Angul', 'Balangir', 'Balasore', 'Bargarh', 'Bhadrak', 'Boudh', 'Cuttack', 'Deogarh', 'Dhenkanal', 'Gajapati', 'Ganjam', 'Jagatsinghpur', 'Jajpur', 'Jharsuguda', 'Kalahandi', 'Kandhamal', 'Kendrapara', 'Kendujhar', 'Khordha', 'Koraput', 'Malkangiri', 'Mayurbhanj', 'Nabarangpur', 'Nayagarh', 'Nuapada', 'Puri', 'Rayagada', 'Sambalpur', 'Subarnapur', 'Sundargarh'],
            'Punjab' => ['Amritsar', 'Barnala', 'Bathinda', 'Faridkot', 'Fatehgarh Sahib', 'Fazilka', 'Ferozepur', 'Gurdaspur', 'Hoshiarpur', 'Jalandhar', 'Kapurthala', 'Ludhiana', 'Malerkotla', 'Mansa', 'Moga', 'Muktsar', 'Pathankot', 'Patiala', 'Rupnagar', 'Sahibzada Ajit Singh Nagar', 'Sangrur', 'Shahid Bhagat Singh Nagar', 'Tarn Taran'],
            'Rajasthan' => ['Ajmer', 'Alwar', 'Banswara', 'Baran', 'Barmer', 'Bharatpur', 'Bhilwara', 'Bikaner', 'Bundi', 'Chittorgarh', 'Churu', 'Dausa', 'Dholpur', 'Dungarpur', 'Hanumangarh', 'Jaipur', 'Jaisalmer', 'Jalore', 'Jhalawar', 'Jhunjhunu', 'Jodhpur', 'Karauli', 'Kota', 'Nagaur', 'Pali', 'Pratapgarh', 'Rajsamand', 'Sawai Madhopur', 'Sikar', 'Sirohi', 'Sri Ganganagar', 'Tonk', 'Udaipur', 'Anupgarh', 'Balotra', 'Beawar', 'Deeg', 'Didwana-Kuchaman', 'Dudu', 'Gangapur City', 'Jaipur Rural', 'Jodhpur Rural', 'Kekri', 'Kotputli-Behror', 'Khairthal-Tijara', 'Neem Ka Thana', 'Phalodi', 'Salumbar', 'Sanchore', 'Shahpura'],
            'Sikkim' => ['Gangtok', 'Mangan', 'Namchi', 'Geyzing', 'Pakyong', 'Soreng'],
            'Tamil Nadu' => ['Ariyalur', 'Chengalpattu', 'Chennai', 'Coimbatore', 'Cuddalore', 'Dharmapuri', 'Dindigul', 'Erode', 'Kallakurichi', 'Kanchipuram', 'Kanyakumari', 'Karur', 'Krishnagiri', 'Madurai', 'Mayiladuthurai', 'Nagapattinam', 'Namakkal', 'Nilgiris', 'Perambalur', 'Pudukkottai', 'Ramanathapuram', 'Ranipet', 'Salem', 'Sivaganga', 'Tenkasi', 'Thanjavur', 'Theni', 'Thoothukudi', 'Tiruchirappalli', 'Tirunelveli', 'Tirupathur', 'Tiruppur', 'Tiruvallur', 'Tiruvannamalai', 'Tiruvarur', 'Vellore', 'Viluppuram', 'Virudhunagar'],
            'Telangana' => ['Adilabad', 'Bhadradri Kothagudem', 'Hanumakonda', 'Hyderabad', 'Jagtial', 'Jangaon', 'Jayashankar Bhupalpally', 'Jogulamba Gadwal', 'Kamareddy', 'Karimnagar', 'Khammam', 'Kumuram Bheem Asifabad', 'Mahabubabad', 'Mahabubnagar', 'Mancherial', 'Medak', 'Medchal-Malkajgiri', 'Mulugu', 'Nagarkurnool', 'Nalgonda', 'Narayanpet', 'Nirmal', 'Nizamabad', 'Peddapalli', 'Rajanna Sircilla', 'Rangareddy', 'Sangareddy', 'Siddipet', 'Suryapet', 'Vikarabad', 'Wanaparthy', 'Warangal', 'Yadadri Bhuvanagiri'],
            'Tripura' => ['Dhalai', 'Gomati', 'Khowai', 'North Tripura', 'Sepahijala', 'South Tripura', 'Unakoti', 'West Tripura'],
            'Uttar Pradesh' => ['Agra', 'Aligarh', 'Ambedkar Nagar', 'Amethi', 'Amroha', 'Auraiya', 'Ayodhya', 'Azamgarh', 'Baghpat', 'Bahraich', 'Ballia', 'Balrampur', 'Banda', 'Barabanki', 'Bareilly', 'Basti', 'Bhadohi', 'Bijnor', 'Budaun', 'Bulandshahr', 'Chandauli', 'Chitrakoot', 'Deoria', 'Etah', 'Etawah', 'Farrukhabad', 'Fatehpur', 'Firozabad', 'Gautam Buddha Nagar', 'Ghaziabad', 'Ghazipur', 'Gonda', 'Gorakhpur', 'Hamirpur', 'Hapur', 'Hardoi', 'Hathras', 'Jalaun', 'Jaunpur', 'Jhansi', 'Kannauj', 'Kanpur Dehat', 'Kanpur Nagar', 'Kasganj', 'Kaushambi', 'Kheri', 'Kushinagar', 'Lalitpur', 'Lucknow', 'Maharajganj', 'Mahoba', 'Mainpuri', 'Mathura', 'Mau', 'Meerut', 'Mirzapur', 'Moradabad', 'Muzaffarnagar', 'Pilibhit', 'Pratapgarh', 'Prayagraj', 'Raebareli', 'Rampur', 'Saharanpur', 'Sambhal', 'Sant Kabir Nagar', 'Shahjahanpur', 'Shamli', 'Shravasti', 'Siddharthnagar', 'Sitapur', 'Sonbhadra', 'Sultanpur', 'Unnao', 'Varanasi'],
            'Uttarakhand' => ['Almora', 'Bageshwar', 'Chamoli', 'Champawat', 'Dehradun', 'Haridwar', 'Nainital', 'Pauri Garhwal', 'Pithoragarh', 'Rudraprayag', 'Tehri Garhwal', 'Udham Singh Nagar', 'Uttarkashi'],
            'West Bengal' => ['Alipurduar', 'Bankura', 'Birbhum', 'Cooch Behar', 'Dakshin Dinajpur', 'Darjeeling', 'Hooghly', 'Howrah', 'Jalpaiguri', 'Jhargram', 'Kalimpong', 'Kolkata', 'Malda', 'Murshidabad', 'Nadia', 'North 24 Parganas', 'Paschim Bardhaman', 'Paschim Medinipur', 'Purba Bardhaman', 'Purba Medinipur', 'Purulia', 'South 24 Parganas', 'Uttar Dinajpur'],
        ];

        $crops = Crop::all()->keyBy('name');
        $currentYear = (int) date('Y');

        $districtIndex = 0;
        foreach ($stateDistricts as $stateName => $districtsList) {
            $stateMetaInfo = $stateMeta[$stateName] ?? ['lat' => 20.0, 'lng' => 78.0, 'zone' => 'Central Zone'];

            $state = Region::firstOrCreate(
                ['name' => $stateName, 'type' => RegionType::State],
                ['parent_id' => $india->id, 'state' => $stateName, 'code' => strtoupper(substr($stateName, 0, 2))]
            );

            foreach ($districtsList as $distName) {
                // Generate slightly offset lat/lng from state center
                $offsetLat = $stateMetaInfo['lat'] + (((($districtIndex * 17) % 100) - 50) / 100.0) * 1.5;
                $offsetLng = $stateMetaInfo['lng'] + (((($districtIndex * 23) % 100) - 50) / 100.0) * 1.5;

                $district = Region::updateOrCreate(
                    [
                        'name' => $distName,
                        'state' => $stateName,
                        'type' => RegionType::District,
                    ],
                    [
                        'parent_id' => $state->id,
                        'code' => strtoupper(substr($distName, 0, 3)) . str_pad($districtIndex % 10, 2, '0', STR_PAD_LEFT),
                        'population' => rand(800000, 3500000),
                        'agricultural_zone' => $stateMetaInfo['zone'],
                        'latitude' => $offsetLat,
                        'longitude' => $offsetLng,
                    ]
                );

                // Seed complex operations data ONLY for the first district of each state to keep execution super fast!
                $isFirstDistrictOfState = ($distName === $districtsList[0]);
                if ($isFirstDistrictOfState) {
                    if (! $district->farmers()->exists() && ! $district->cropPatterns()->exists()) {
                        $this->seedDistrictData($district, $districtIndex, $crops, $currentYear);
                    }
                }

                $districtIndex++;
            }
        }

        User::updateOrCreate(
            ['email' => 'superadmin@agrolens.gov.in'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'role' => UserRole::SuperAdmin,
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'officer@agrolens.gov.in'],
            [
                'name' => 'District Agriculture Officer',
                'password' => Hash::make('password'),
                'role' => UserRole::GovernmentOfficer,
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'viewer@agrolens.gov.in'],
            [
                'name' => 'Public Research Viewer',
                'password' => Hash::make('password'),
                'role' => UserRole::PublicViewer,
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
    }

    /**
     * @param  Collection<string, Crop>  $crops
     */
    private function seedDistrictData(Region $district, int $index, Collection $crops, int $currentYear): void
    {
        $categories = LandHoldingCategory::cases();
        $sources = IrrigationSourceType::cases();
        $seasons = CropSeason::cases();
        $preferredCrops = self::REGIONAL_CROPS[$district->state] ?? $crops->keys()->take(3)->all();
        $availableCrops = collect($preferredCrops)
            ->filter(fn (string $name) => $crops->has($name))
            ->map(fn (string $name) => $crops[$name]);

        if ($availableCrops->isEmpty()) {
            $availableCrops = $crops->take(3);
        }

        for ($f = 1; $f <= 25; $f++) {
            $farmer = Farmer::create([
                'farmer_code' => strtoupper(substr($district->code ?? 'REG', 0, 3)).'-'.str_pad((string) ($index * 100 + $f), 5, '0', STR_PAD_LEFT),
                'name' => fake()->name(),
                'phone' => fake()->numerify('98########'),
                'region_id' => $district->id,
                'ownership_type' => fake()->randomElement(['owner', 'tenant', 'sharecropper']),
                'household_size' => rand(3, 8),
                'latitude' => $district->latitude + fake()->randomFloat(4, -0.05, 0.05),
                'longitude' => $district->longitude + fake()->randomFloat(4, -0.05, 0.05),
            ]);

            $holdings = rand(1, 3);
            for ($h = 0; $h < $holdings; $h++) {
                $area = fake()->randomFloat(2, 0.4, 12);
                $category = $area < 1 ? LandHoldingCategory::Marginal
                    : ($area < 2 ? LandHoldingCategory::Small
                    : ($area < 4 ? LandHoldingCategory::SemiMedium
                    : ($area < 10 ? LandHoldingCategory::Medium : LandHoldingCategory::Large)));

                $holding = LandHolding::create([
                    'farmer_id' => $farmer->id,
                    'region_id' => $district->id,
                    'area_hectares' => $area,
                    'category' => $category,
                    'soil_type' => fake()->randomElement(['Alluvial', 'Black', 'Red', 'Laterite']),
                    'is_irrigated' => fake()->boolean(70),
                    'is_fragmented' => fake()->boolean(30),
                    'fragment_count' => rand(1, 4),
                ]);

                IrrigationRecord::create([
                    'land_holding_id' => $holding->id,
                    'region_id' => $district->id,
                    'source_type' => fake()->randomElement($sources),
                    'water_availability_score' => fake()->randomFloat(1, 3, 9),
                    'efficiency_percent' => fake()->randomFloat(1, 40, 95),
                    'water_stress' => fake()->boolean(20),
                    'groundwater_level_m' => fake()->randomFloat(1, 5, 45),
                ]);

                $cropCount = min(rand(1, 2), $availableCrops->count());
                $selectedCrops = $availableCrops->random($cropCount);
                $weights = $selectedCrops->mapWithKeys(fn (Crop $crop) => [
                    $crop->id => fake()->randomFloat(2, 0.2, 1.0),
                ]);
                $weightSum = $weights->sum();

                foreach ($selectedCrops as $crop) {
                    $share = $weights[$crop->id] / $weightSum;
                    $cropArea = round($area * $share, 4);

                    CropPattern::create([
                        'region_id' => $district->id,
                        'crop_id' => $crop->id,
                        'land_holding_id' => $holding->id,
                        'season' => fake()->randomElement($seasons),
                        'year' => $currentYear,
                        'area_hectares' => $cropArea,
                        'yield_quintals' => fake()->randomFloat(1, 8, 45),
                        'fertilizer_usage_kg' => fake()->randomFloat(1, 20, 200),
                    ]);
                }
            }

            if ($f % 5 === 0) {
                Well::create([
                    'region_id' => $district->id,
                    'well_type' => fake()->randomElement(['bore_well', 'tube_well', 'dug_well']),
                    'depth_feet' => rand(40, 650),
                    'water_table_level_m' => fake()->randomFloat(1, 8, 35),
                    'recharge_status' => fake()->randomElement(['good', 'moderate', 'poor']),
                    'alert_low_groundwater' => fake()->boolean(15),
                    'latitude' => $farmer->latitude,
                    'longitude' => $farmer->longitude,
                ]);
            }
        }
    }
}
