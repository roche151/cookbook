<x-app-layout>
	<x-slot name="title">Home</x-slot>

	<div class="container py-5">
		<!-- Hero -->
		<div class="row align-items-center gy-4 mb-5">
			<div class="col-md-6">
				<h1 class="display-5 fw-bold">{{ config('app.name', 'Cookbook') }}</h1>
				<p class="lead text-muted">Organize recipes, discover new dishes, and keep favorites at your fingertips.</p>

				<form action="/recipes" method="GET" class="mt-4">
					<div class="input-group input-group-lg">
						<input name="q" type="search" class="form-control" placeholder="Search recipes, ingredients, or tags" aria-label="Search recipes">
						<button class="btn btn-primary" type="submit" aria-label="Search">
							<i class="fa-solid fa-magnifying-glass"></i>
						</button>
					</div>
					<div class="form-text mt-2 text-muted">Try: "chocolate", "gluten-free", "30 minute"</div>
				</form>

				<div class="mt-4">
					<a href="/recipes/create" class="btn btn-outline-primary me-2">
						<i class="fa-solid fa-plus me-2"></i>Create Recipe
					</a>
					<a href="/recipes" class="btn btn-link text-decoration-none">Browse all recipes</a>
				</div>
			</div>

			<div class="col-md-6 text-center">
				<img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1200&q=80&auto=format&fit=crop" alt="Delicious food" class="img-fluid rounded shadow" style="max-height:360px; object-fit:cover;">
			</div>
		</div>


		<x-featured-recipes />

		<!-- Categories -->
		<div class="mb-5">
			<h3 class="h5">Browse by category</h3>
			<div class="mt-3">
				<a href="/recipes?category=breakfast" class="btn btn-sm btn-outline-secondary me-2 mb-2"><i class="fa-solid fa-mug-hot me-1"></i> Breakfast</a>
				<a href="/recipes?category=lunch" class="btn btn-sm btn-outline-secondary me-2 mb-2"><i class="fa-solid fa-bowl-food me-1"></i> Lunch</a>
				<a href="/recipes?category=dinner" class="btn btn-sm btn-outline-secondary me-2 mb-2"><i class="fa-solid fa-utensils me-1"></i> Dinner</a>
				<a href="/recipes?category=dessert" class="btn btn-sm btn-outline-secondary me-2 mb-2"><i class="fa-solid fa-cake-candles me-1"></i> Desserts</a>
				<a href="/recipes?category=vegetarian" class="btn btn-sm btn-outline-secondary me-2 mb-2"><i class="fa-solid fa-leaf me-1"></i> Vegetarian</a>
			</div>
		</div>

		<!-- CTA / Newsletter -->
		<div class="p-4 rounded-3 bg-body border">
			<div class="row align-items-center">
				<div class="col-md-8">
					<h4 class="mb-1">Get new recipes delivered weekly</h4>
					<p class="mb-0 text-muted">Join our community for exclusive seasonal recipes and tips.</p>
				</div>
				<div class="col-md-4 mt-3 mt-md-0 text-md-end">
					<form class="d-flex" action="/subscribe" method="POST">
						<input type="email" name="email" class="form-control me-2" placeholder="you@example.com" aria-label="Email for newsletter">
						<button class="btn btn-primary" type="submit">Subscribe</button>
					</form>
				</div>
			</div>
		</div>
	</div>

</x-app-layout>