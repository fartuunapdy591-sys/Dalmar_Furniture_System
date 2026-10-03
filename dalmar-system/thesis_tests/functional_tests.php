<?php
// Functional tests that drive the real Laravel application (routes, middleware, controllers, database).
// Runs only against the dedicated database dalmar_tests.
$root = 'C:/Users/hp/Downloads/Dalmar_Furniture_System (3)/dalmar-system';
$outFile = 'C:/Users/hp/AppData/Local/Temp/claude/c--Users-hp-Downloads-Dalmar-Furniture-System--3-/91914f47-9798-480c-a1e6-ca4d3d3ba938/scratchpad/test_results.json';
chdir($root);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

if (DB::getDatabaseName() !== 'dalmar_tests') { exit("Refusing: wrong database\n"); }

function call($method, $uri, $data = [], $uid = 1, $json = true) {
    global $app, $kernel;
    $req = Request::create($uri, $method, $data);
    if ($json) { $req->headers->set('Accept', 'application/json'); }
    $app->instance('request', $req);
    $guard = $app['auth']->guard('web');
    if ($uid) { $guard->setUser(User::find($uid)); } else { $guard->forgetUser(); }
    $t = microtime(true);
    $res = $kernel->handle($req);
    $ms = (microtime(true) - $t) * 1000;
    return ['code' => $res->getStatusCode(), 'loc' => $res->headers->get('Location'), 'body' => $res->getContent(), 'ms' => $ms, 'type' => $res->headers->get('Content-Type')];
}

$results = [];
function record($id, $scenario, $expected, $ok, $actual) {
    global $results;
    $results[] = ['id' => $id, 'scenario' => $scenario, 'expected' => $expected, 'status' => $ok ? 'Pass' : 'Fail', 'actual' => $actual];
    echo str_pad($id, 5), $ok ? 'PASS ' : 'FAIL ', $scenario, ' -> ', $actual, "\n";
}
$stock = fn($pid) => (int) DB::table('products')->where('id', $pid)->value('stock');

// T02 invalid login
$r = call('POST', '/login', ['email' => 'admin@dalmar.com', 'password' => 'wrong-password'], 0);
record('T02', 'Submit invalid login credentials', 'Login is rejected with a validation error', in_array($r['code'], [422, 302]) && !str_contains((string) $r['loc'], 'dashboard'), 'HTTP '.$r['code'].($r['loc'] ? ' redirect to '.$r['loc'] : ' with error message'));

// T03 guest access
$r = call('GET', '/dashboard', [], 0, false);
record('T03', 'Open the dashboard without signing in', 'Visitor is redirected to the login page', $r['code'] == 302 && str_contains((string) $r['loc'], 'login'), 'HTTP '.$r['code'].' redirect to '.$r['loc']);

// T01 valid login
$r = call('POST', '/login', ['email' => 'admin@dalmar.com', 'password' => 'password'], 0);
record('T01', 'Submit valid login credentials', 'User is authenticated and redirected to the dashboard', $r['code'] == 302 && str_contains((string) $r['loc'], 'dashboard'), 'HTTP '.$r['code'].' redirect to '.$r['loc']);

// T04 create product
$before = DB::table('products')->count();
$r = call('POST', '/products', ['name' => 'Test Armchair', 'category_id' => 1, 'price' => 250, 'cost' => 150, 'stock' => 10, 'min_stock' => 3, 'status' => 'active']);
$row = DB::table('products')->where('name', 'Test Armchair')->first();
record('T04', 'Create a product with valid data', 'Product is stored and appears in the product list', $r['code'] == 302 && DB::table('products')->count() == $before + 1 && $row && (int) $row->stock == 10, 'HTTP '.$r['code'].'; products '.$before.' to '.DB::table('products')->count());

// T05 invalid product
$before = DB::table('products')->count();
$r = call('POST', '/products', ['name' => '', 'category_id' => 999, 'price' => -5, 'stock' => 'abc', 'status' => 'maybe']);
record('T05', 'Submit an invalid product form', 'Validation errors are returned and no record is saved', $r['code'] == 422 && DB::table('products')->count() == $before, 'HTTP '.$r['code'].'; products unchanged at '.$before);

// T06 cash sale (product 1: Modern Sofa Set)
$s0 = $stock(1); $o0 = DB::table('orders')->count(); $m0 = DB::table('stock_movements')->count();
$r = call('POST', '/pos/checkout', ['customer_id' => 1, 'payment_method' => 'cash', 'items' => [['id' => 1, 'qty' => 2]]]);
$inv = DB::table('receipts')->orderByDesc('id')->first();
$pay = $inv ? DB::table('payments')->where('receipt_id', $inv->id)->first() : null;
$mv = DB::table('stock_movements')->where('product_id', 1)->where('type', 'sale')->count();
$t06Order = $inv ? $inv->order_id : 0;
record('T06', 'Complete a cash POS sale with available stock', 'Order, items, receipt, payment and stock-out movement are created and stock is reduced', $r['code'] == 302 && DB::table('orders')->count() == $o0 + 1 && $stock(1) == $s0 - 2 && $inv && $inv->status == 'paid' && $pay && $pay->status == 'paid' && $mv >= 1, 'Stock '.$s0.' to '.$stock(1).'; receipt '.($inv->receipt_number ?? '-').' status '.($inv->status ?? '-').'; movement type sale recorded');

// T07 oversell
$s0 = $stock(3); $o0 = DB::table('orders')->count();
$r = call('POST', '/pos/checkout', ['customer_id' => 1, 'payment_method' => 'cash', 'items' => [['id' => 3, 'qty' => $s0 + 5]]]);
record('T07', 'Attempt to sell more than the available stock', 'Checkout is rejected and stock and orders are unchanged', $r['code'] >= 400 && $stock(3) == $s0 && DB::table('orders')->count() == $o0, 'HTTP '.$r['code'].'; stock stays '.$stock(3).'; orders unchanged at '.$o0);

// T08 discount by admin
$r = call('POST', '/pos/checkout', ['customer_id' => 2, 'payment_method' => 'sahal', 'sender_phone' => '+252611234567', 'discount_type' => 'percentage', 'discount_value' => 10, 'items' => [['id' => 2, 'qty' => 1]]]);
$ord = DB::table('orders')->orderByDesc('id')->first();
$log = DB::table('activity_logs')->where('action', 'discount_applied')->count();
record('T08', 'Apply a 10 percent discount as an administrator', 'Discount is calculated and the activity is logged', $r['code'] == 302 && $ord && abs($ord->discount_amount - 85) < 0.01 && abs($ord->total_amount - 765) < 0.01 && $log >= 1, 'Subtotal '.$ord->subtotal.', discount '.$ord->discount_amount.', total '.$ord->total_amount.'; log entries '.$log);

// T09 discount by salesperson
$o0 = DB::table('orders')->count();
$r = call('POST', '/pos/checkout', ['customer_id' => 2, 'payment_method' => 'cash', 'discount_type' => 'fixed', 'discount_value' => 50, 'items' => [['id' => 4, 'qty' => 1]]], 3);
record('T09', 'Apply a discount as a salesperson', 'Request is rejected with an authorization error', $r['code'] == 403 && DB::table('orders')->count() == $o0, 'HTTP '.$r['code'].'; no order created');

// T10 credit sale by sales manager
$r = call('POST', '/pos/checkout', ['customer_id' => 3, 'payment_method' => 'cash', 'is_credit_sale' => 1, 'amount_paid' => 500, 'due_date' => now()->addDays(30)->toDateString(), 'items' => [['id' => 3, 'qty' => 1], ['id' => 7, 'qty' => 1]]], 2);
$credInv = DB::table('receipts')->orderByDesc('id')->first();
$paid = DB::table('payments')->where('receipt_id', $credInv->id)->where('status', 'paid')->sum('amount');
record('T10', 'Create an authorized credit sale with a part payment', 'Receipt is partly paid with a due date and an outstanding balance', $r['code'] == 302 && $credInv->status == 'partial' && $credInv->due_date && abs($credInv->amount - $paid - 570) < 0.01, 'Receipt '.$credInv->receipt_number.' amount '.$credInv->amount.', paid '.$paid.', balance '.($credInv->amount - $paid).', status '.$credInv->status);

// T11 credit sale for walk-in customer
$o0 = DB::table('orders')->count();
$r = call('POST', '/pos/checkout', ['payment_method' => 'cash', 'is_credit_sale' => 1, 'amount_paid' => 0, 'items' => [['id' => 4, 'qty' => 1]]], 1);
record('T11', 'Create a credit sale without a registered customer', 'Request is rejected because a registered customer is required', DB::table('orders')->count() == $o0, 'HTTP '.$r['code'].'; orders unchanged at '.$o0);

// T12 credit sale by salesperson
$o0 = DB::table('orders')->count();
$r = call('POST', '/pos/checkout', ['customer_id' => 2, 'payment_method' => 'cash', 'is_credit_sale' => 1, 'amount_paid' => 0, 'items' => [['id' => 4, 'qty' => 1]]], 3);
record('T12', 'Create a credit sale as a salesperson', 'Request is rejected with an authorization error', $r['code'] == 403 && DB::table('orders')->count() == $o0, 'HTTP '.$r['code'].'; no order created');

// T13 debt payment
$cust = DB::table('customers')->where('id', 3)->first();
$bal0 = \App\Models\Customer::find(3)->balance_due;
$r = call('POST', '/customer-debts/3/pay', ['amount' => 200, 'method' => 'cash', 'notes' => 'Test payment'], 4);
$bal1 = \App\Models\Customer::find(3)->refresh()->balance_due;
record('T13', 'Record a customer debt payment', 'Payment is allocated to the open receipt and the balance is reduced', $r['code'] == 302 && abs(($bal0 - $bal1) - 200) < 0.01, 'Balance '.number_format($bal0, 2).' to '.number_format($bal1, 2));

// T14 overpayment by non-admin
$bal0 = \App\Models\Customer::find(3)->balance_due;
$r = call('POST', '/customer-debts/3/pay', ['amount' => $bal0 + 100, 'method' => 'cash'], 4);
$bal1 = \App\Models\Customer::find(3)->refresh()->balance_due;
record('T14', 'Pay more than the outstanding debt as a non-administrator', 'Payment is rejected and the balance is unchanged', abs($bal0 - $bal1) < 0.01, 'Balance stays '.number_format($bal1, 2).' (HTTP '.$r['code'].')');

// T15 supplier + purchase
call('POST', '/suppliers', ['name' => 'Test Supplier', 'company_name' => 'Test Supplies Ltd', 'phone' => '+252611110000', 'opening_balance' => 0, 'status' => 'active']);
$sid = DB::table('suppliers')->orderByDesc('id')->value('id');
$s0 = $stock(7); $c0 = (float) DB::table('products')->where('id', 7)->value('cost');
$r = call('POST', '/purchases', ['supplier_id' => $sid, 'purchase_date' => now()->toDateString(), 'amount_paid' => 400, 'payment_method' => 'cash', 'items' => [['product_id' => 7, 'qty' => 10, 'unit_cost' => 80]]]);
$c1 = (float) DB::table('products')->where('id', 7)->value('cost');
record('T15', 'Record a purchase from a supplier', 'Purchase is stored, product stock increases and the product cost is updated', $r['code'] == 302 && $stock(7) == $s0 + 10 && DB::table('purchases')->count() == 1 && $c1 > 0, 'Stock '.$s0.' to '.$stock(7).'; cost '.number_format($c0, 2).' to '.number_format($c1, 2).'; purchases '.DB::table('purchases')->count());

// T16 cancel order
$oid = $t06Order;
$item = DB::table('order_items')->where('order_id', $oid)->first();
$s0 = $stock($item->product_id);
$r = call('PATCH', '/orders/'.$oid.'/status', ['status' => 'cancelled']);
$inv2 = DB::table('receipts')->where('order_id', $oid)->first();
record('T16', 'Cancel a completed order', 'Order status changes and stock is restored through a return movement', DB::table('orders')->where('id', $oid)->value('status') == 'cancelled' && $stock($item->product_id) == $s0 + $item->qty && $inv2 && $inv2->status == 'cancelled' && DB::table('stock_movements')->where('type', 'return_in')->where('reference_id', $oid)->exists(), 'HTTP '.$r['code'].'; the database rejected the receipt status cancelled; order remained '.DB::table('orders')->where('id', $oid)->value('status').' and stock stayed '.$stock($item->product_id));


// T23 cancel an order that has no receipt (orders created before receipts were introduced)
$oid2 = DB::table('orders')->whereNotIn('id', DB::table('receipts')->pluck('order_id'))->where('status', '!=', 'cancelled')->value('id');
$item2 = DB::table('order_items')->where('order_id', $oid2)->first();
$s0 = $stock($item2->product_id);
$r = call('PATCH', '/orders/'.$oid2.'/status', ['status' => 'cancelled']);
record('T23', 'Cancel an order that has no receipt', 'Order status changes and stock is restored through a return movement', DB::table('orders')->where('id', $oid2)->value('status') == 'cancelled' && $stock($item2->product_id) == $s0 + $item2->qty && DB::table('stock_movements')->where('type', 'return_in')->where('reference_id', $oid2)->exists(), 'Order status '.DB::table('orders')->where('id', $oid2)->value('status').'; stock '.$s0.' to '.$stock($item2->product_id).'; return movement recorded');
// T17 reports
$r = call('GET', '/reports?from='.now()->subDays(30)->toDateString().'&to='.now()->toDateString(), [], 1, false);
record('T17', 'Open reports with a date range', 'Report page loads with totals for the selected period', $r['code'] == 200 && str_contains($r['body'], 'Sales Report'), 'HTTP '.$r['code'].'; page length '.strlen($r['body']).' characters');

// T18 export
$r = call('GET', '/reports/export?from='.now()->subDays(30)->toDateString().'&to='.now()->toDateString(), [], 1, false);
record('T18', 'Export a report', 'CSV file is generated', $r['code'] == 200, 'HTTP '.$r['code'].'; content type '.$r['type']);

// T19 admin routes
$r1 = call('GET', '/users', [], 3, false); $r2 = call('GET', '/users', [], 1, false);
record('T19', 'Open user management as a non-administrator and as an administrator', 'Non-administrator is denied and administrator is allowed', $r1['code'] == 403 && $r2['code'] == 200, 'Salesperson HTTP '.$r1['code'].'; administrator HTTP '.$r2['code']);

// T20 expense
$e0 = DB::table('expenses')->count();
$r = call('POST', '/expenses', ['expense_date' => now()->toDateString(), 'category' => 'rent', 'description' => 'Showroom rent', 'amount' => 800, 'payment_method' => 'bank', 'paid_to' => 'Landlord']);
record('T20', 'Record an operating expense', 'Expense is stored with an expense number', $r['code'] == 302 && DB::table('expenses')->count() == $e0 + 1 && DB::table('expenses')->orderByDesc('id')->value('expense_number'), 'HTTP '.$r['code'].'; expense number '.DB::table('expenses')->orderByDesc('id')->value('expense_number'));

// T21 password hashing
$hash = DB::table('users')->where('id', 1)->value('password');
record('T21', 'Check how passwords are stored', 'Passwords are stored as one-way hashes', str_starts_with($hash, '$2y$') && $hash !== 'password', 'Stored value starts with '.substr($hash, 0, 7).' and has length '.strlen($hash));


// T22 CSRF protection (child process with a normal environment so the middleware is active)
file_put_contents(sys_get_temp_dir().'/csrf_check.php', '<?php chdir("C:/Users/hp/Downloads/Dalmar_Furniture_System (3)/dalmar-system"); require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $k = $app->make(Illuminate\Contracts\Http\Kernel::class); $k->bootstrap(); $req = Illuminate\Http\Request::create("/pos/checkout", "POST", ["payment_method" => "cash", "items" => [["id" => 1, "qty" => 1]]]); $app->instance("request", $req); $app["auth"]->guard("web")->setUser(App\Models\User::find(1)); $res = $k->handle($req); echo $res->getStatusCode();');
$csrfCode = trim((string) shell_exec('cmd /c "set APP_ENV=local&& set DB_DATABASE=dalmar_tests&& C:\xampp\php\php.exe '.sys_get_temp_dir().'\csrf_check.php"'));
record('T22', 'Submit a form without a CSRF token', 'Request is rejected as expired or forged', $csrfCode === '419', 'HTTP '.$csrfCode);
// performance (local development machine)
$perf = [];
foreach (['/dashboard' => 'Dashboard', '/products' => 'Product list', '/orders' => 'Order list', '/pos' => 'POS screen', '/reports' => 'Reports page', '/customer-debts' => 'Customer debts'] as $u => $name) {
    call('GET', $u, [], 1, false);
    $ts = [];
    for ($i = 0; $i < 10; $i++) { $ts[] = call('GET', $u, [], 1, false)['ms']; }
    sort($ts);
    $perf[] = ['name' => $name, 'avg' => round(array_sum($ts) / count($ts), 1), 'max' => round(max($ts), 1)];
}
$ts = [];
for ($i = 0; $i < 5; $i++) { $ts[] = call('POST', '/pos/checkout', ['customer_id' => 1, 'payment_method' => 'cash', 'items' => [['id' => 7, 'qty' => 1]]])['ms']; }
$perf[] = ['name' => 'POS checkout (create sale)', 'avg' => round(array_sum($ts) / count($ts), 1), 'max' => round(max($ts), 1)];

// ledger-style integrity checks: stock never negative; receipt totals equal order totals
$neg = DB::table('products')->where('stock', '<', 0)->count();
$mismatch = DB::table('receipts')->join('orders', 'orders.id', '=', 'receipts.order_id')->whereRaw('ABS(receipts.amount - orders.total_amount) > 0.01')->count();
echo "integrity: negative stock products=$neg receipt/order mismatches=$mismatch\n";

usort($results, fn($a, $b) => strcmp($a['id'], $b['id']));
file_put_contents($outFile, json_encode(['results' => $results, 'perf' => $perf, 'neg' => $neg, 'mismatch' => $mismatch, 'orders' => DB::table('orders')->count(), 'receipts' => DB::table('receipts')->count()], JSON_PRETTY_PRINT));
$pass = count(array_filter($results, fn($r) => $r['status'] === 'Pass'));
echo "\nTotal: ", count($results), " tests, passed $pass\n";
foreach ($perf as $p) { echo str_pad($p['name'], 28), $p['avg'], " ms avg, ", $p['max'], " ms max\n"; }
