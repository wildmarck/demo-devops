terraform {
  required_providers {
    docker = {
      source  = "kreuzwerker/docker"
      version = "~> 3.0.1"
    }
  }
}

# On laisse vide pour utiliser la config par défaut du système
# (Ça marche sur GitHub Actions ET sur ton Docker Desktop)
provider "docker" {}

resource "docker_image" "meteo_image" {
  name         = "wildmarckgamming/meteo-app:latest" # Ton image Docker Hub
  keep_locally = false
}

resource "docker_container" "meteo_container" {
  image = docker_image.meteo_image.image_id
  name  = "mon_site_terraform"
  
  ports {
    internal = 80
    external = 8080
  }
}