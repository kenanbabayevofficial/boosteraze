.class public Lcom/mikasa/codm/Preferences;
.super Ljava/lang/Object;


# static fields
.field public static a:Landroid/content/Context;

.field public static b:Z

.field public static c:Z

.field private static d:Landroid/content/SharedPreferences;

.field private static e:Lcom/mikasa/codm/Preferences;

.field private static final short:[S


# direct methods
.method static constructor <clinit>()V
    .locals 1

    const/16 v0, 0xc

    new-array v0, v0, [S

    fill-array-data v0, :array_0

    sput-object v0, Lcom/mikasa/codm/Preferences;->short:[S

    return-void

    :array_0
    .array-data 2
        0x1c3s
        0x1ecs
        0x1ees
        0x1f9s
        0x1fas
        0x1f9s
        0x1ees
        0x1f9s
        0x1f2s
        0x1ffs
        0x1f9s
        0x1efs
    .end array-data
.end method

.method native constructor <init>(Landroid/content/Context;)V
.end method

.method public static native Changes(Landroid/content/Context;ILjava/lang/String;IZLjava/lang/String;)V
.end method

.method public static native a(Ljava/lang/String;I)I
.end method

.method public static native a(Landroid/content/Context;)Lcom/mikasa/codm/Preferences;
.end method

.method public static native a(Ljava/lang/String;II)V
.end method

.method public static native a(Ljava/lang/String;ILjava/lang/String;)V
.end method

.method public static native a(Ljava/lang/String;IZ)V
.end method

.method public static native b(Ljava/lang/String;I)Ljava/lang/String;
.end method

.method public static native b(Ljava/lang/String;IZ)Z
.end method

.method public static native ۣ۟ۤ۠ۥ()[S
.end method

.method public static native ۟ۤۥ۟۟()Lcom/mikasa/codm/Preferences;
.end method

.method public static native ۣ۟ۧۤۢ()Landroid/content/SharedPreferences;
.end method


# virtual methods
.method public native a(I)Ljava/lang/String;
.end method

.method public native a()V
.end method

.method public native a(II)V
.end method

.method public native a(ILjava/lang/String;)V
.end method

.method public native a(IZ)Z
.end method

.method public native b(I)I
.end method

.method public native b(IZ)V
.end method
