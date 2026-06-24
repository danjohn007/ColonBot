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

        // Cargar el límite de Colón desde OSM (server-side para evitar CORS)
        $boundaryData = $this->fetchColonBoundary();

        $this->view('map.index', compact('categories', 'preloadId', 'preloadCat', 'boundaryData'));
    }

    /** API: devuelve POIs en JSON para el mapa */
    public function poi(): void
    {
        $filters    = [
            'category' => $_GET['category'] ?? '',
            'search'   => $_GET['q'] ?? '',
        ];
        $businesses = $this->businesses->withFilters($filters);

        $pois = array_map(function($b) {
            $tripTypes = array_column($this->businesses->tripTypes((int)$b['id']), 'trip_type');
            return [
                'id'             => $b['id'],
                'name'           => $b['name'],
                'slug'           => $b['slug'],
                'lat'            => (float)$b['lat'],
                'lng'            => (float)$b['lng'],
                'category'       => $b['category_name'],
                'category_slug'  => $b['category_slug'] ?? '',
                'category_color' => $b['category_color'],
                'category_icon'  => $b['category_icon'],
                'rating'         => (float)$b['rating'],
                'cover'          => $b['cover_image'] ? imageUrl($b['cover_image']) : asset('img/placeholder.svg'),
                'url'            => url('lugar/' . $b['slug']),
                'isotipo'        => $b['isotipo'] ?? '',
                'trip_types'     => $tripTypes,
            ];
        }, $businesses);

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

        $images    = $this->businesses->images($business['id']);
        $amenities = $this->businesses->amenities($business['id']);
        $services  = $this->businesses->services($business['id']);
        $products  = $this->businesses->products($business['id']);
        $events    = $this->businesses->allEvents($business['id']);
        $tripTypes = array_column($this->businesses->tripTypes((int)$business['id']), 'trip_type');
        $reviews   = $this->businesses->reviews((int)$business['id']);

        $this->view('map.detail', compact('business', 'images', 'amenities', 'services', 'products', 'events', 'tripTypes', 'reviews'));
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

    /**
     * Obtiene las coordenadas del límite de Colón desde Nominatim (server-side)
     */
    private function fetchColonBoundary(): string
    {
        $url = 'https://nominatim.openstreetmap.org/lookup?osm_ids=R2671516&format=geojson';
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 10,
                'header' => "User-Agent: ColonBot/1.0\r\n",
            ],
        ]);

        $json = @file_get_contents($url, false, $ctx);
        if ($json === false) {
            // Fallback: coordenadas hardcodeadas extraídas de OSM
            return json_encode([
                [20.8851, -100.1853], [20.8934, -100.1547], [20.8812, -100.0987],
                [20.8342, -100.0234], [20.7894, -99.9876], [20.7456, -99.9642],
                [20.7123, -99.9912], [20.6789, -100.0234], [20.6845, -100.1234],
                [20.7234, -100.1876], [20.7689, -100.1923], [20.8234, -100.1943],
                [20.8851, -100.1853],
            ]);
        }

        $data = json_decode($json, true);
        if (!$data || empty($data['features'])) {
            return '[]';
        }

        $feat = $data['features'][0];
        $coords = $feat['geometry']['coordinates'];
        $ring = $feat['geometry']['type'] === 'MultiPolygon' ? $coords[0][0] : $coords[0];

        $latlngs = array_map(function($c) {
            return [$c[1], $c[0]];
        }, $ring);

        return json_encode($latlngs);
    }
}