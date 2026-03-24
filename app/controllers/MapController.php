<?php
class MapController extends Controller
{
    private BusinessModel  $businesses;
    private CategoryModel  $categories;
    private AnalyticsModel $analytics;

    public function __construct()
    {
        $this->businesses = new BusinessModel();
        $this->categories = new CategoryModel();
        $this->analytics  = new AnalyticsModel();
    }

    public function index(string $id = ''): void
    {
        $categories = $this->categories->active();
        $this->analytics->track('map_view');
        $preloadId  = (is_numeric($id) && (int)$id > 0) ? (int)$id : 0;
        $preloadCat = (!is_numeric($id) && $id !== '') ? $id : '';
        $this->view('map.index', compact('categories', 'preloadId', 'preloadCat'));
    }

    /** API: devuelve POIs en JSON para el mapa */
    public function poi(): void
    {
        $filters    = [
            'category' => $_GET['category'] ?? '',
            'search'   => $_GET['q'] ?? '',
        ];
        $businesses = $this->businesses->withFilters($filters);

        $pois = array_map(fn($b) => [
            'id'             => $b['id'],
            'name'           => $b['name'],
            'slug'           => $b['slug'],
            'lat'            => (float)$b['lat'],
            'lng'            => (float)$b['lng'],
            'category'       => $b['category_name'],
            'category_color' => $b['category_color'],
            'category_icon'  => $b['category_icon'],
            'rating'         => (float)$b['rating'],
            'cover'          => $b['cover_image'] ? imageUrl($b['cover_image']) : asset('img/placeholder.svg'),
            'url'            => url('lugar/' . $b['slug']),
        ], $businesses);

        $this->json($pois);
    }

    public function detail(string $slug): void
    {
        $business = $this->businesses->findBySlug($slug);
        if (!$business) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }
        $this->businesses->incrementVisit($business['id']);
        $this->analytics->track('map_view', $business['id']);

        $images   = $this->businesses->images($business['id']);
        $amenities = $this->businesses->amenities($business['id']);
        $services  = $this->businesses->services($business['id']);
        $products  = $this->businesses->products($business['id']);
        $events    = $this->businesses->allEvents($business['id']);

        $this->view('map.detail', compact('business', 'images', 'amenities', 'services', 'products', 'events'));
    }

    public function contact(string $slug): void
    {
        $business = $this->businesses->findBySlug($slug);
        if (!$business) {
            $this->json(['error' => 'not found'], 404);
        }
        $this->analytics->track('whatsapp_click', $business['id']);
        $this->json(['ok' => true]);
    }
}
