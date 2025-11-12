# 🎯 Guide d'intégration Frontend Angular - Module Commercial

## 📋 Vue d'ensemble

Ce guide détaille l'intégration du **système de parrainage commercial** dans l'application Angular frontend. Le système permet aux commerciaux de créer des comptes clients et de suivre leurs performances via des codes parrainage uniques.

## 🔧 Configuration de base

### 1. Variables d'environnement

Ajoutez ces variables dans votre fichier `environment.ts` :

```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api',
  apiKey: 'your_api_key_here', // Clé API obligatoire
  // ... autres variables
};
```

### 2. Service HTTP de base

Créez un service de base pour gérer les requêtes API :

```typescript
// services/api.service.ts
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private baseUrl = environment.apiUrl;
  private apiKey = environment.apiKey;

  constructor(private http: HttpClient) {}

  private getHeaders(): HttpHeaders {
    const token = localStorage.getItem('access_token');
    return new HttpHeaders({
      'X-API-Key': this.apiKey,
      'Authorization': token ? `Bearer ${token}` : '',
      'Content-Type': 'application/json'
    });
  }

  get<T>(endpoint: string): Observable<T> {
    return this.http.get<T>(`${this.baseUrl}${endpoint}`, {
      headers: this.getHeaders()
    });
  }

  post<T>(endpoint: string, data: any): Observable<T> {
    return this.http.post<T>(`${this.baseUrl}${endpoint}`, data, {
      headers: this.getHeaders()
    });
  }

  put<T>(endpoint: string, data: any): Observable<T> {
    return this.http.put<T>(`${this.baseUrl}${endpoint}`, data, {
      headers: this.getHeaders()
    });
  }

  delete<T>(endpoint: string): Observable<T> {
    return this.http.delete<T>(`${this.baseUrl}${endpoint}`, {
      headers: this.getHeaders()
    });
  }
}
```

## 🏗️ Modèles TypeScript

### 1. Modèle Commercial

```typescript
// models/commercial.model.ts
export interface Commercial {
  id: number;
  email: string;
  code_parrainage_commercial: string;
  personne: {
    nom: string;
    prenoms: string;
  };
}

export interface CommercialStats {
  total_clients: number;
  clients_actifs: number;
  clients_physiques: number;
  clients_moraux: number;
  taux_activation: number;
}
```

### 2. Modèle Client

```typescript
// models/client.model.ts
export interface Client {
  id: number;
  email: string;
  contact: string;
  est_actif: boolean;
  compte_cree_par_commercial: boolean;
  commercial_id?: number;
  code_parrainage?: string;
  personne: {
    nom: string;
    prenoms?: string;
    date_naissance?: string;
    sexe?: 'M' | 'F';
    profession?: string;
  };
  client: {
    type_client: 'physique' | 'moral';
  };
}

export interface CreateClientRequest {
  type_demandeur: 'client';
  type_client: 'physique' | 'moral';
  email: string;
  contact: string;
  adresse: string;
  nom: string;
  prenoms?: string; // Requis pour physique
  date_naissance?: string; // Requis pour physique
  sexe?: 'M' | 'F'; // Requis pour physique
  profession?: string;
  photo?: File; // Optionnel
}
```

### 3. Modèles de réponse API

```typescript
// models/api-response.model.ts
export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}
```

## 🎯 Services Angular

### 1. Service Commercial

```typescript
// services/commercial.service.ts
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { Commercial, CommercialStats, Client, CreateClientRequest } from '../models';

@Injectable({
  providedIn: 'root'
})
export class CommercialService {
  constructor(private apiService: ApiService) {}

  /**
   * Génère un code parrainage unique pour le commercial connecté
   */
  genererCodeParrainage(): Observable<ApiResponse<{code_parrainage: string, commercial: Commercial}>> {
    return this.apiService.post('/v1/commercial/generer-code-parrainage', {});
  }

  /**
   * Crée un compte client avec mot de passe généré automatiquement
   */
  creerCompteClient(clientData: CreateClientRequest): Observable<ApiResponse<{client: Client, mot_de_passe_genere: string}>> {
    return this.apiService.post('/v1/commercial/creer-compte-client', clientData);
  }

  /**
   * Récupère la liste des clients parrainés par le commercial
   */
  mesClientsParraines(): Observable<ApiResponse<{clients: Client[], total: number}>> {
    return this.apiService.get('/v1/commercial/mes-clients-parraines');
  }

  /**
   * Récupère les statistiques du commercial
   */
  mesStatistiques(): Observable<ApiResponse<{statistiques: CommercialStats, commercial: Commercial}>> {
    return this.apiService.get('/v1/commercial/mes-statistiques');
  }
}
```

### 2. Service d'authentification (mis à jour)

```typescript
// services/auth.service.ts
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { ApiService } from './api.service';

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  type_demandeur: 'client' | 'prestataire';
  type_client?: 'physique' | 'moral';
  email: string;
  password: string;
  contact: string;
  adresse: string;
  nom: string;
  prenoms?: string;
  date_naissance?: string;
  sexe?: 'M' | 'F';
  profession?: string;
  photo?: File;
  code_parrainage?: string; // NOUVEAU : Code parrainage optionnel
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  constructor(private apiService: ApiService) {}

  login(credentials: LoginRequest): Observable<any> {
    return this.apiService.post('/v1/auth/login', credentials)
      .pipe(
        tap(response => {
          if (response.success) {
            localStorage.setItem('access_token', response.data.access_token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
          }
        })
      );
  }

  register(userData: RegisterRequest): Observable<any> {
    return this.apiService.post('/v1/auth/register', userData);
  }

  logout(): void {
    localStorage.removeItem('access_token');
    localStorage.removeItem('user');
  }

  isLoggedIn(): boolean {
    return !!localStorage.getItem('access_token');
  }

  getCurrentUser(): any {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }
}
```

## 🎨 Composants Angular

### 1. Dashboard Commercial

```typescript
// components/commercial-dashboard/commercial-dashboard.component.ts
import { Component, OnInit } from '@angular/core';
import { CommercialService } from '../../services/commercial.service';
import { CommercialStats, Client } from '../../models';

@Component({
  selector: 'app-commercial-dashboard',
  templateUrl: './commercial-dashboard.component.html',
  styleUrls: ['./commercial-dashboard.component.css']
})
export class CommercialDashboardComponent implements OnInit {
  stats: CommercialStats | null = null;
  clients: Client[] = [];
  codeParrainage: string = '';
  loading = false;

  constructor(private commercialService: CommercialService) {}

  ngOnInit(): void {
    this.loadStats();
    this.loadClients();
  }

  loadStats(): void {
    this.loading = true;
    this.commercialService.mesStatistiques().subscribe({
      next: (response) => {
        this.stats = response.data.statistiques;
        this.codeParrainage = response.data.commercial.code_parrainage_commercial;
        this.loading = false;
      },
      error: (error) => {
        console.error('Erreur lors du chargement des statistiques:', error);
        this.loading = false;
      }
    });
  }

  loadClients(): void {
    this.commercialService.mesClientsParraines().subscribe({
      next: (response) => {
        this.clients = response.data.clients;
      },
      error: (error) => {
        console.error('Erreur lors du chargement des clients:', error);
      }
    });
  }

  genererNouveauCode(): void {
    this.commercialService.genererCodeParrainage().subscribe({
      next: (response) => {
        this.codeParrainage = response.data.code_parrainage;
        alert('Nouveau code parrainage généré: ' + this.codeParrainage);
      },
      error: (error) => {
        console.error('Erreur lors de la génération du code:', error);
      }
    });
  }
}
```

```html
<!-- components/commercial-dashboard/commercial-dashboard.component.html -->
<div class="commercial-dashboard">
  <div class="header">
    <h1>Dashboard Commercial</h1>
    <div class="code-parrainage">
      <label>Code Parrainage:</label>
      <span class="code">{{codeParrainage}}</span>
      <button (click)="genererNouveauCode()" class="btn btn-primary">
        Générer nouveau code
      </button>
    </div>
  </div>

  <!-- Statistiques -->
  <div class="stats-grid" *ngIf="stats">
    <div class="stat-card">
      <h3>Total Clients</h3>
      <div class="stat-value">{{stats.total_clients}}</div>
    </div>
    <div class="stat-card">
      <h3>Clients Actifs</h3>
      <div class="stat-value">{{stats.clients_actifs}}</div>
    </div>
    <div class="stat-card">
      <h3>Taux d'Activation</h3>
      <div class="stat-value">{{stats.taux_activation}}%</div>
    </div>
    <div class="stat-card">
      <h3>Clients Physiques</h3>
      <div class="stat-value">{{stats.clients_physiques}}</div>
    </div>
    <div class="stat-card">
      <h3>Clients Moraux</h3>
      <div class="stat-value">{{stats.clients_moraux}}</div>
    </div>
  </div>

  <!-- Liste des clients -->
  <div class="clients-section">
    <h2>Mes Clients Parrainés</h2>
    <div class="clients-grid">
      <div class="client-card" *ngFor="let client of clients">
        <div class="client-info">
          <h4>{{client.personne.prenoms}} {{client.personne.nom}}</h4>
          <p>{{client.email}}</p>
          <p>{{client.contact}}</p>
          <span class="badge" [class.active]="client.est_actif">
            {{client.est_actif ? 'Actif' : 'Inactif'}}
          </span>
        </div>
      </div>
    </div>
  </div>
</div>
```

### 2. Formulaire de création de client

```typescript
// components/create-client/create-client.component.ts
import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { CommercialService } from '../../services/commercial.service';
import { CreateClientRequest } from '../../models';

@Component({
  selector: 'app-create-client',
  templateUrl: './create-client.component.html',
  styleUrls: ['./create-client.component.css']
})
export class CreateClientComponent {
  clientForm: FormGroup;
  loading = false;
  successMessage = '';

  constructor(
    private fb: FormBuilder,
    private commercialService: CommercialService
  ) {
    this.clientForm = this.createForm();
  }

  createForm(): FormGroup {
    return this.fb.group({
      type_client: ['physique', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      contact: ['', Validators.required],
      adresse: ['', Validators.required],
      nom: ['', Validators.required],
      prenoms: [''],
      date_naissance: [''],
      sexe: [''],
      profession: [''],
      photo: [null]
    });
  }

  onTypeClientChange(): void {
    const typeClient = this.clientForm.get('type_client')?.value;
    
    if (typeClient === 'physique') {
      this.clientForm.get('prenoms')?.setValidators([Validators.required]);
      this.clientForm.get('date_naissance')?.setValidators([Validators.required]);
      this.clientForm.get('sexe')?.setValidators([Validators.required]);
    } else {
      this.clientForm.get('prenoms')?.clearValidators();
      this.clientForm.get('date_naissance')?.clearValidators();
      this.clientForm.get('sexe')?.clearValidators();
    }
    
    this.clientForm.get('prenoms')?.updateValueAndValidity();
    this.clientForm.get('date_naissance')?.updateValueAndValidity();
    this.clientForm.get('sexe')?.updateValueAndValidity();
  }

  onFileSelected(event: any): void {
    const file = event.target.files[0];
    if (file) {
      this.clientForm.patchValue({ photo: file });
    }
  }

  onSubmit(): void {
    if (this.clientForm.valid) {
      this.loading = true;
      const formData = this.clientForm.value;
      
      const clientData: CreateClientRequest = {
        type_demandeur: 'client',
        type_client: formData.type_client,
        email: formData.email,
        contact: formData.contact,
        adresse: formData.adresse,
        nom: formData.nom,
        prenoms: formData.prenoms,
        date_naissance: formData.date_naissance,
        sexe: formData.sexe,
        profession: formData.profession,
        photo: formData.photo
      };

      this.commercialService.creerCompteClient(clientData).subscribe({
        next: (response) => {
          this.successMessage = `Client créé avec succès! Mot de passe: ${response.data.mot_de_passe_genere}`;
          this.clientForm.reset();
          this.loading = false;
        },
        error: (error) => {
          console.error('Erreur lors de la création du client:', error);
          this.loading = false;
        }
      });
    }
  }
}
```

```html
<!-- components/create-client/create-client.component.html -->
<div class="create-client-form">
  <h2>Créer un nouveau client</h2>
  
  <form [formGroup]="clientForm" (ngSubmit)="onSubmit()">
    <!-- Type de client -->
    <div class="form-group">
      <label>Type de client</label>
      <select formControlName="type_client" (change)="onTypeClientChange()">
        <option value="physique">Client Physique</option>
        <option value="moral">Client Moral (Entreprise)</option>
      </select>
    </div>

    <!-- Informations communes -->
    <div class="form-group">
      <label>Email *</label>
      <input type="email" formControlName="email" />
    </div>

    <div class="form-group">
      <label>Contact *</label>
      <input type="tel" formControlName="contact" />
    </div>

    <div class="form-group">
      <label>Adresse *</label>
      <textarea formControlName="adresse"></textarea>
    </div>

    <div class="form-group">
      <label>Nom *</label>
      <input type="text" formControlName="nom" />
    </div>

    <!-- Informations spécifiques au client physique -->
    <div *ngIf="clientForm.get('type_client')?.value === 'physique'">
      <div class="form-group">
        <label>Prénoms *</label>
        <input type="text" formControlName="prenoms" />
      </div>

      <div class="form-group">
        <label>Date de naissance *</label>
        <input type="date" formControlName="date_naissance" />
      </div>

      <div class="form-group">
        <label>Sexe *</label>
        <select formControlName="sexe">
          <option value="M">Masculin</option>
          <option value="F">Féminin</option>
        </select>
      </div>

      <div class="form-group">
        <label>Profession</label>
        <input type="text" formControlName="profession" />
      </div>

      <div class="form-group">
        <label>Photo</label>
        <input type="file" (change)="onFileSelected($event)" accept="image/*" />
      </div>
    </div>

    <button type="submit" [disabled]="clientForm.invalid || loading">
      {{loading ? 'Création...' : 'Créer le client'}}
    </button>
  </form>

  <div *ngIf="successMessage" class="success-message">
    {{successMessage}}
  </div>
</div>
```

### 3. Formulaire d'inscription avec code parrainage

```typescript
// components/register/register.component.ts
import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css']
})
export class RegisterComponent {
  registerForm: FormGroup;
  loading = false;
  successMessage = '';

  constructor(
    private fb: FormBuilder,
    private authService: AuthService
  ) {
    this.registerForm = this.createForm();
  }

  createForm(): FormGroup {
    return this.fb.group({
      type_demandeur: ['client', Validators.required],
      type_client: ['physique', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(8)]],
      contact: ['', Validators.required],
      adresse: ['', Validators.required],
      nom: ['', Validators.required],
      prenoms: [''],
      date_naissance: [''],
      sexe: [''],
      profession: [''],
      photo: [null],
      code_parrainage: [''] // NOUVEAU : Code parrainage optionnel
    });
  }

  onTypeClientChange(): void {
    const typeClient = this.registerForm.get('type_client')?.value;
    
    if (typeClient === 'physique') {
      this.registerForm.get('prenoms')?.setValidators([Validators.required]);
      this.registerForm.get('date_naissance')?.setValidators([Validators.required]);
      this.registerForm.get('sexe')?.setValidators([Validators.required]);
    } else {
      this.registerForm.get('prenoms')?.clearValidators();
      this.registerForm.get('date_naissance')?.clearValidators();
      this.registerForm.get('sexe')?.clearValidators();
    }
    
    this.registerForm.get('prenoms')?.updateValueAndValidity();
    this.registerForm.get('date_naissance')?.updateValueAndValidity();
    this.registerForm.get('sexe')?.updateValueAndValidity();
  }

  onSubmit(): void {
    if (this.registerForm.valid) {
      this.loading = true;
      const formData = this.registerForm.value;
      
      this.authService.register(formData).subscribe({
        next: (response) => {
          this.successMessage = 'Inscription réussie! Vérifiez votre email pour valider votre compte.';
          this.registerForm.reset();
          this.loading = false;
        },
        error: (error) => {
          console.error('Erreur lors de l\'inscription:', error);
          this.loading = false;
        }
      });
    }
  }
}
```

```html
<!-- components/register/register.component.html -->
<div class="register-form">
  <h2>Inscription</h2>
  
  <form [formGroup]="registerForm" (ngSubmit)="onSubmit()">
    <!-- Code parrainage (optionnel) -->
    <div class="form-group">
      <label>Code Parrainage (optionnel)</label>
      <input type="text" formControlName="code_parrainage" 
             placeholder="Entrez le code parrainage si vous en avez un" />
      <small class="help-text">
        Si vous avez un code parrainage d'un commercial SUNU Santé, saisissez-le ici.
      </small>
    </div>

    <!-- Reste du formulaire... -->
    <!-- Type de client, email, mot de passe, etc. -->
    
    <button type="submit" [disabled]="registerForm.invalid || loading">
      {{loading ? 'Inscription...' : 'S\'inscrire'}}
    </button>
  </form>

  <div *ngIf="successMessage" class="success-message">
    {{successMessage}}
  </div>
</div>
```

## 🛣️ Configuration des routes

```typescript
// app-routing.module.ts
import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { CommercialDashboardComponent } from './components/commercial-dashboard/commercial-dashboard.component';
import { CreateClientComponent } from './components/create-client/create-client.component';
import { RegisterComponent } from './components/register/register.component';

const routes: Routes = [
  // Routes commerciales (protégées)
  {
    path: 'commercial',
    children: [
      { path: 'dashboard', component: CommercialDashboardComponent },
      { path: 'create-client', component: CreateClientComponent }
    ],
    canActivate: [AuthGuard] // Garde d'authentification
  },
  
  // Route d'inscription publique
  { path: 'register', component: RegisterComponent },
  
  // ... autres routes
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
```

## 🔒 Garde d'authentification

```typescript
// guards/auth.guard.ts
import { Injectable } from '@angular/core';
import { CanActivate, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {
  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  canActivate(): boolean {
    if (this.authService.isLoggedIn()) {
      const user = this.authService.getCurrentUser();
      // Vérifier si l'utilisateur est un commercial
      if (user && user.roles && user.roles.includes('commercial')) {
        return true;
      }
    }
    
    this.router.navigate(['/login']);
    return false;
  }
}
```

## 🎨 Styles CSS

```css
/* styles/commercial.css */
.commercial-dashboard {
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.code-parrainage {
  display: flex;
  align-items: center;
  gap: 10px;
}

.code {
  font-family: monospace;
  font-weight: bold;
  background: #f0f0f0;
  padding: 5px 10px;
  border-radius: 4px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  text-align: center;
}

.stat-value {
  font-size: 2em;
  font-weight: bold;
  color: #2c5aa0;
}

.clients-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.client-card {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.8em;
  background: #dc3545;
  color: white;
}

.badge.active {
  background: #28a745;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.success-message {
  background: #d4edda;
  color: #155724;
  padding: 10px;
  border-radius: 4px;
  margin-top: 20px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-primary {
  background: #2c5aa0;
  color: white;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
```

## 📱 Gestion des erreurs

```typescript
// services/error-handler.service.ts
import { Injectable } from '@angular/core';
import { HttpErrorResponse } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class ErrorHandlerService {
  handleError(error: HttpErrorResponse): string {
    if (error.error && error.error.message) {
      return error.error.message;
    }
    
    if (error.status === 401) {
      return 'Non autorisé. Veuillez vous reconnecter.';
    }
    
    if (error.status === 403) {
      return 'Accès interdit. Vous n\'avez pas les permissions nécessaires.';
    }
    
    if (error.status === 422) {
      return 'Données invalides. Veuillez vérifier vos informations.';
    }
    
    return 'Une erreur est survenue. Veuillez réessayer.';
  }
}
```

## 🧪 Tests unitaires

```typescript
// services/commercial.service.spec.ts
import { TestBed } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { CommercialService } from './commercial.service';

describe('CommercialService', () => {
  let service: CommercialService;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [CommercialService]
    });
    service = TestBed.inject(CommercialService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });

  it('should generate parrainage code', () => {
    // Test de génération de code parrainage
  });

  it('should create client account', () => {
    // Test de création de compte client
  });
});
```

## 🚀 Déploiement

### 1. Build de production

```bash
ng build --prod
```

### 2. Variables d'environnement de production

```typescript
// environments/environment.prod.ts
export const environment = {
  production: true,
  apiUrl: 'https://api.sunusante.com/api',
  apiKey: 'production_api_key',
};
```

## 📋 Checklist d'intégration

- [ ] ✅ Service API de base configuré
- [ ] ✅ Modèles TypeScript créés
- [ ] ✅ Service Commercial implémenté
- [ ] ✅ Dashboard Commercial créé
- [ ] ✅ Formulaire de création de client
- [ ] ✅ Formulaire d'inscription avec code parrainage
- [ ] ✅ Garde d'authentification
- [ ] ✅ Routes configurées
- [ ] ✅ Styles CSS appliqués
- [ ] ✅ Gestion des erreurs
- [ ] ✅ Tests unitaires
- [ ] ✅ Build de production

## 🔗 Endpoints API utilisés

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/v1/commercial/generer-code-parrainage` | Génère un code parrainage |
| POST | `/v1/commercial/creer-compte-client` | Crée un compte client |
| GET | `/v1/commercial/mes-clients-parraines` | Liste des clients parrainés |
| GET | `/v1/commercial/mes-statistiques` | Statistiques commerciales |
| POST | `/v1/auth/register` | Inscription avec code parrainage |

## 📞 Support

Pour toute question ou problème :
- 📧 Email : dev@sunusante.com
- 📱 Téléphone : +225 XX XX XX XX
- 💬 Slack : #frontend-support

---

**Note** : Ce guide est basé sur Angular 15+ et utilise les dernières pratiques recommandées. Adaptez selon votre version d'Angular.

