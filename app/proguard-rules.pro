# Add project specific ProGuard rules here.
# You can control the set of applied configuration files using the
# proguardFiles setting in build.gradle.
#
# For more details, see
#   http://developer.android.com/guide/developing/tools/proguard.html

# Security: Keep security classes
-keep class com.snaptikpro.app.security.** { *; }
-keepclassmembers class com.snaptikpro.app.security.** { *; }

# Security: Keep network classes
-keep class com.snaptikpro.app.network.** { *; }
-keepclassmembers class com.snaptikpro.app.network.** { *; }

# Security: Keep utility classes
-keep class com.snaptikpro.app.utils.** { *; }
-keepclassmembers class com.snaptikpro.app.utils.** { *; }

# Security: Keep Retrofit classes
-keepattributes Signature
-keepattributes *Annotation*
-keep class retrofit2.** { *; }
-keepclasseswithmembers class * {
    @retrofit2.http.* <methods>;
}

# Security: Keep OkHttp classes
-keep class okhttp3.** { *; }
-keep interface okhttp3.** { *; }
-dontwarn okhttp3.**

# Security: Keep Gson classes
-keep class com.google.gson.** { *; }
-keep class * implements com.google.gson.TypeAdapterFactory
-keep class * implements com.google.gson.JsonSerializer
-keep class * implements com.google.gson.JsonDeserializer

# Security: Keep Glide classes
-keep public class * implements com.bumptech.glide.module.GlideModule
-keep class * extends com.bumptech.glide.module.AppGlideModule {
 <init>(...);
}
-keep public enum com.bumptech.glide.load.ImageHeaderParser$** {
  **[] $VALUES;
  public *;
}

# Security: Keep Media3 classes
-keep class androidx.media3.** { *; }
-keep interface androidx.media3.** { *; }

# Security: Keep Lottie classes
-keep class com.airbnb.lottie.** { *; }

# Security: Keep SecurePreferences
-keep class com.scottyab.securepreferences.** { *; }

# Security: Keep SQLCipher
-keep class net.sqlcipher.** { *; }
-keep class net.sqlcipher.database.** { *; }

# Security: Obfuscate sensitive strings
-keepclassmembers class * {
    @android.webkit.JavascriptInterface <methods>;
}

# Security: Remove debug information
-renamesourcefileattribute SourceFile
-keepattributes SourceFile,LineNumberTable

# Security: Remove logging
-assumenosideeffects class android.util.Log {
    public static *** d(...);
    public static *** v(...);
}

# Security: Remove unused classes
-dontwarn android.support.**
-dontwarn androidx.**
-dontwarn org.conscrypt.**
-dontwarn org.bouncycastle.**
-dontwarn org.openjsse.**

# Security: Optimize
-optimizations !code/simplification/arithmetic,!code/simplification/cast,!field/*,!class/merging/*
-optimizationpasses 5
-allowaccessmodification

# Security: Keep main activities
-keep class com.snaptikpro.app.MainActivity { *; }
-keep class com.snaptikpro.app.SplashActivity { *; }
-keep class com.snaptikpro.app.DownloadsActivity { *; }
-keep class com.snaptikpro.app.VideoPlayerActivity { *; }
-keep class com.snaptikpro.app.SettingsActivity { *; }