<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    use ApiResponse;

    /**
     * Resolve Customer for the Authenticated User
     */
    protected function resolveCustomer(Request $request): ?Customer
    {
        $user = Auth::guard('api')->user() ?? $request->user();

        if (!$user) {
            return null;
        }

        $customer = Customer::where('user_id', $user->id)->first();

        if (!$customer) {
            // Find by email or create new customer profile
            $customer = Customer::where('email', $user->email)->first();

            if ($customer) {
                $customer->user_id = $user->id;
                $customer->save();
            } else {
                $customer = Customer::create([
                    'user_id'           => $user->id,
                    'customer_group_id' => 1,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'phone_number'      => $user->phone ?? '0000000000',
                    'is_active'         => 1,
                ]);
            }
        }

        return $customer;
    }

    /**
     * Get All Saved Addresses for Authenticated Customer
     */
    public function index(Request $request)
    {
        try {
            $customer = $this->resolveCustomer($request);
            if (!$customer) {
                return $this->unauthorized('Please log in to manage your addresses');
            }

            $addresses = CustomerAddress::where('customer_id', $customer->id)
                ->orderBy('default', 'desc')
                ->orderBy('id', 'desc')
                ->get()
                ->map(fn($addr) => $this->formatAddress($addr));

            return $this->success([
                'addresses' => $addresses,
            ], 'Addresses retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve addresses: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Parse and normalize input from Request (JSON, Form-Data, Query, Raw Body)
     */
    protected function getRequestData(Request $request): array
    {
        $data = [];

        // 1. Check Laravel's parsed inputs
        $all = $request->all();
        if (is_array($all) && !empty($all)) {
            $data = array_merge($data, $all);
        }

        // 2. Check native PHP superglobals
        if (!empty($_POST) && is_array($_POST)) {
            $data = array_merge($data, $_POST);
        }
        if (!empty($_GET) && is_array($_GET)) {
            $data = array_merge($data, $_GET);
        }
        if (!empty($_REQUEST) && is_array($_REQUEST)) {
            $data = array_merge($data, $_REQUEST);
        }

        // 3. Check JSON bag
        $json = $request->json() ? $request->json()->all() : [];
        if (is_array($json) && !empty($json)) {
            $data = array_merge($data, $json);
        }

        // 4. Check raw body stream
        $raw = $request->getContent();
        if (empty($raw)) {
            $raw = @file_get_contents('php://input');
        }

        if (!empty($raw) && is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded)) {
                $data = array_merge($data, $decoded);
            } elseif (str_contains($raw, 'name=')) {
                // Universal Multipart Boundary Parser for PUT/PATCH form-data
                if (preg_match_all('/name=["\']?([^"\';\r\n]+)["\']?.*?(?:\r?\n\r?\n|\n\n)(.*?)(?:\r?\n--|\n--|$)/s', $raw, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $m) {
                        $key = trim($m[1]);
                        $val = trim($m[2]);
                        $data[$key] = $val;
                    }
                }
            } else {
                parse_str($raw, $parsed);
                if (is_array($parsed) && !empty($parsed)) {
                    $data = array_merge($data, $parsed);
                }
            }
        }

        // 5. Query parameters
        $query = $request->query();
        if (is_array($query) && !empty($query)) {
            $data = array_merge($data, $query);
        }

        return $data;
    }

    /**
     * Store New Customer Address
     */
    public function store(Request $request)
    {
        $data = $this->getRequestData($request);
        $request->merge($data);

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:191',
            'phone'       => 'required|string|max:191',
            'email'       => 'nullable|email|max:191',
            'address'     => 'required|string|max:255',
            'city'        => 'required|string|max:191',
            'state'       => 'nullable|string|max:191',
            'country'     => 'nullable|string|max:191',
            'zip'         => 'nullable|string|max:50',
            'is_default'  => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            $customer = $this->resolveCustomer($request);
            if (!$customer) {
                return $this->unauthorized('Please log in to add an address');
            }

            $isDefault = isset($data['is_default']) 
                ? (filter_var($data['is_default'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ($data['is_default'] == 1))
                : (isset($data['default']) ? (filter_var($data['default'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ($data['default'] == 1)) : false);

            // If it's the first address, make it default automatically
            $addressCount = CustomerAddress::where('customer_id', $customer->id)->count();
            if ($addressCount === 0) {
                $isDefault = true;
            }

            DB::beginTransaction();

            if ($isDefault) {
                CustomerAddress::where('customer_id', $customer->id)->update(['default' => 0]);
            }

            $address = CustomerAddress::create([
                'customer_id' => $customer->id,
                'name'        => trim($data['name'] ?? $request->name),
                'phone'       => trim($data['phone'] ?? $request->phone),
                'email'       => !empty($data['email']) ? trim($data['email']) : $customer->email,
                'address'     => trim($data['address'] ?? $request->address),
                'city'        => trim($data['city'] ?? $request->city),
                'state'       => !empty($data['state']) ? trim($data['state']) : null,
                'country'     => !empty($data['country']) ? trim($data['country']) : 'Bangladesh',
                'zip'         => !empty($data['zip']) ? trim($data['zip']) : null,
                'default'     => $isDefault ? 1 : 0,
            ]);

            if ($isDefault) {
                $customer->update([
                    'address'     => $address->address,
                    'city'        => $address->city,
                    'state'       => $address->state,
                    'country'     => $address->country,
                    'postal_code' => $address->zip,
                ]);
            }

            DB::commit();

            return $this->success([
                'address' => $this->formatAddress($address),
            ], 'Address created successfully', 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Failed to create address: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Show Specific Address
     */
    public function show(Request $request, $id)
    {
        try {
            $customer = $this->resolveCustomer($request);
            if (!$customer) {
                return $this->unauthorized();
            }

            $address = CustomerAddress::where('customer_id', $customer->id)->where('id', $id)->first();
            if (!$address) {
                return $this->error('Address not found', 404);
            }

            return $this->success([
                'address' => $this->formatAddress($address),
            ], 'Address retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve address: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update Address
     */
    public function update(Request $request, $id)
    {
        $data = $this->getRequestData($request);
        $request->merge($data);

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|required|string|max:191',
            'phone'       => 'sometimes|required|string|max:191',
            'email'       => 'nullable|email|max:191',
            'address'     => 'sometimes|required|string|max:255',
            'city'        => 'sometimes|required|string|max:191',
            'state'       => 'nullable|string|max:191',
            'country'     => 'nullable|string|max:191',
            'zip'         => 'nullable|string|max:50',
            'is_default'  => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            $customer = $this->resolveCustomer($request);
            if (!$customer) {
                return $this->unauthorized();
            }

            $address = CustomerAddress::where('customer_id', $customer->id)->where('id', $id)->first();
            if (!$address) {
                return $this->error('Address not found', 404);
            }

            $isDefault = isset($data['is_default']) 
                ? (filter_var($data['is_default'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ($data['is_default'] == 1))
                : (isset($data['default']) ? (filter_var($data['default'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ($data['default'] == 1)) : null);

            DB::beginTransaction();

            if ($isDefault === true) {
                CustomerAddress::where('customer_id', $customer->id)->where('id', '!=', $address->id)->update(['default' => 0]);
                $address->default = 1;
            } elseif ($isDefault === false) {
                $address->default = 0;
            }

            $fillable = ['name', 'phone', 'email', 'address', 'city', 'state', 'country', 'zip'];
            foreach ($fillable as $field) {
                if (array_key_exists($field, $data) && $data[$field] !== null) {
                    $address->{$field} = trim((string)$data[$field]);
                }
            }

            $address->save();

            if ($address->default) {
                $customer->update([
                    'address'     => $address->address,
                    'city'        => $address->city,
                    'state'       => $address->state,
                    'country'     => $address->country,
                    'postal_code' => $address->zip,
                ]);
            }

            DB::commit();

            return $this->success([
                'address' => $this->formatAddress($address),
            ], 'Address updated successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Failed to update address: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete Address
     */
    public function destroy(Request $request, $id)
    {
        try {
            $customer = $this->resolveCustomer($request);
            if (!$customer) {
                return $this->unauthorized();
            }

            $address = CustomerAddress::where('customer_id', $customer->id)->where('id', $id)->first();
            if (!$address) {
                return $this->error('Address not found', 404);
            }

            $wasDefault = (bool)$address->default;
            $address->delete();

            // If the deleted address was default, make the most recent remaining address default
            if ($wasDefault) {
                $nextAddress = CustomerAddress::where('customer_id', $customer->id)->orderBy('id', 'desc')->first();
                if ($nextAddress) {
                    $nextAddress->default = 1;
                    $nextAddress->save();

                    $customer->update([
                        'address'     => $nextAddress->address,
                        'city'        => $nextAddress->city,
                        'state'       => $nextAddress->state,
                        'country'     => $nextAddress->country,
                        'postal_code' => $nextAddress->zip,
                    ]);
                }
            }

            return $this->success([], 'Address deleted successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete address: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Set Address as Default
     */
    public function setDefault(Request $request, $id)
    {
        try {
            $customer = $this->resolveCustomer($request);
            if (!$customer) {
                return $this->unauthorized();
            }

            $address = CustomerAddress::where('customer_id', $customer->id)->where('id', $id)->first();
            if (!$address) {
                return $this->error('Address not found', 404);
            }

            DB::beginTransaction();

            CustomerAddress::where('customer_id', $customer->id)->update(['default' => 0]);
            $address->default = 1;
            $address->save();

            $customer->update([
                'address'     => $address->address,
                'city'        => $address->city,
                'state'       => $address->state,
                'country'     => $address->country,
                'postal_code' => $address->zip,
            ]);

            DB::commit();

            return $this->success([
                'address' => $this->formatAddress($address),
            ], 'Default address updated');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Failed to set default address: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Format Address JSON Output
     */
    public function formatAddress(CustomerAddress $address): array
    {
        return [
            'id'          => $address->id,
            'customer_id' => $address->customer_id,
            'name'        => $address->name,
            'phone'       => $address->phone,
            'email'       => $address->email,
            'address'     => $address->address,
            'city'        => $address->city,
            'state'       => $address->state,
            'country'     => $address->country,
            'zip'         => $address->zip,
            'is_default'  => (bool) $address->default,
            'created_at'  => $address->created_at ? $address->created_at->toIso8601String() : null,
        ];
    }
}
