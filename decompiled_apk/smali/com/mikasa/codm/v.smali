.class Lcom/mikasa/codm/v;
.super Landroid/os/Handler;


# static fields
.field private static final short:[S


# instance fields
.field private final a:Lcom/mikasa/codm/u;

.field private final b:Landroid/app/ProgressDialog;


# direct methods
.method static constructor <clinit>()V
    .locals 1

    const/16 v0, 0x14

    new-array v0, v0, [S

    fill-array-data v0, :array_0

    sput-object v0, Lcom/mikasa/codm/v;->short:[S

    return-void

    :array_0
    .array-data 2
        0xc56s
        0xc5as
        0xc58s
        0xc1bs
        0xc52s
        0xc54s
        0xc47s
        0xc50s
        0xc5bs
        0xc54s
        0xc1bs
        0xc52s
        0xc54s
        0xc58s
        0xc50s
        0xc1bs
        0xc56s
        0xc5as
        0xc51s
        0xc58s
    .end array-data
.end method

.method native constructor <init>(Lcom/mikasa/codm/u;Landroid/app/ProgressDialog;)V
.end method

.method public static native ۟ۡۢۧ۟(Ljava/lang/Object;)Lcom/mikasa/codm/u;
.end method

.method public static native ۢۢۢۤ(Ljava/lang/Object;)Lcom/mikasa/codm/MainActivity;
.end method

.method public static native ۣۢۧۧ(Ljava/lang/Object;)V
.end method

.method public static native ۣۤۦۣ()[S
.end method

.method public static native ۤۡ۟ۢ(Ljava/lang/Object;)Landroid/app/ProgressDialog;
.end method


# virtual methods
.method public native handleMessage(Landroid/os/Message;)V
    .annotation runtime Ljava/lang/Override;
    .end annotation
.end method
